import { Request } from 'lightna/lightna-engine/lib/Request';
import { $ } from 'lightna/lightna-engine/lib/utils/dom';
import { Blocks } from 'lightna/lightna-engine/lib/Blocks';
import { PageMessage } from 'lightna/magento-frontend/common/PageMessage';

export class MiniCart {
    blockId = 'minicart';
    removeFromCartUrl = '/checkout/sidebar/removeItem';
    minActionDuration = 200;
    classes = {
        cartOpen: 'minicart-open',
        fade: 'fade-out',
    };
    actions = {
        'open-minicart': () => this.open(),
        'close-minicart': () => this.close(),
        'remove-product': (trigger) => this.removeProduct(trigger),
    };

    constructor() {
        this.miniCart = '.cjs-minicart';
        this.initializeEventListeners();
    }

    initializeEventListeners() {
        document.addEventListener('add-to-cart', (event) => this.handleAddToCart(event));
        document.addEventListener('keydown', (event) => this.handleKeydown(event));
        $('body').addEventListener('click', (event) => this.handleCartActions(event));
    }

    handleCartActions(event) {
        const trigger = event.target.closest(`[data-action]`);
        if (!trigger) return;

        const action = trigger.getAttribute('data-action');
        const handler = this.actions[action];
        if (handler) {
            try {
                handler(trigger);
            } catch (error) {
                console.error(`Error handling minicart action: ${action}`, error);
            }
        }
    }

    handleKeydown(event) {
        if (event.key === 'Escape') {
            this.close();
        }
    }

    getContentElement() {
        return $('#minicart-content');
    }

    async refresh() {
        try {
            await Blocks.updateHtml([this.blockId]);
        } catch (error) {
            console.error('Error refreshing the minicart:', error);
        }
    }

    async handleAddToCart(event) {
        await this.refresh();
        if (!event.detail.withMessages) {
            await this.open();
        }
    }

    async open() {
        if (!this.getContentElement()) {
            await this.refresh();
        }

        setTimeout(() => {
            PageMessage.clearAll();
            document.body.classList.add(this.classes.cartOpen);
        }, this.minActionDuration);
    }

    close() {
        document.body.classList.remove(this.classes.cartOpen);
    }

    async removeProduct(trigger) {
        const itemId = trigger.getAttribute('data-item-id');
        if (!itemId) return;

        try {
            await Request.post(this.removeFromCartUrl, { item_id: itemId });
            this.afterRemoveProduct(itemId);
        } catch (error) {
            console.error(`Error removing product (ID: ${itemId}) from minicart:`, error);
        }
    }

    afterRemoveProduct(itemId) {
        this.fadeOutItem(itemId);

        setTimeout(() => {
            this.refresh();
        }, this.minActionDuration);
    }

    fadeOutItem(itemId) {
        const removedItem = $(`[data-item-id="${itemId}"]`, $(this.miniCart))?.closest('li');
        if (removedItem) {
            removedItem.classList.add(this.classes.fade);
        }
    }
}
