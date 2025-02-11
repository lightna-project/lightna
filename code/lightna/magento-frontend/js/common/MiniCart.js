import { Request } from 'lightna/lightna-engine/lib/Request';
import { $ } from 'lightna/lightna-engine/lib/utils/dom';
import { Blocks } from 'lightna/lightna-engine/lib/Blocks';
import { PageMessage } from 'lightna/magento-frontend/common/PageMessage';
import { ClickEventDelegator} from 'lightna/magento-frontend/common/ClickEventDelegator';

export class MiniCart {
    blockId = 'minicart';
    removeFromCartUrl = '/checkout/sidebar/removeItem';
    minActionDuration = 200;
    classes = {
        cartOpen: 'minicart-open',
        fade: 'fade-out',
    };
    actions = {
        click: {
            'open-minicart': [() => this.open()],
            'close-minicart': [() => this.close()],
            'remove-product': [(event, trigger) => this.removeProduct(trigger)],
        }
    };

    constructor() {
        this.component = '.cjs-minicart';
        this.initializeEventListeners();
        this.initializeActions();
    }

    initializeEventListeners() {
        document.addEventListener('add-to-cart', (event) => this.handleAddToCart(event));
        document.addEventListener('keydown', (event) => this.handleKeydown(event));
    }

    initializeActions() {
        ClickEventDelegator.add(this.actions.click);
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
        if (!event.detail.response.messagesHtml) {
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
        const removedItem = $(`[data-item-id="${itemId}"]`, $(this.component))?.closest('li');
        if (removedItem) {
            removedItem.classList.add(this.classes.fade);
        }
    }
}
