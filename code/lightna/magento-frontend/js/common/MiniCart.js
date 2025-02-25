import { Request } from 'lightna/engine/lib/Request';
import { $ } from 'lightna/engine/lib/utils/dom';
import { Blocks } from 'lightna/engine/lib/Blocks';
import { PageMessage } from 'lightna/magento-frontend/common/PageMessage';
import { ClickEventDelegator} from 'lightna/magento-frontend/common/ClickEventDelegator';

export class MiniCart {
    static MINICART_BLOCK_ID = 'minicart';
    static CART_REMOVE_URL = '/checkout/sidebar/removeItem';
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
    component = '.cjs-minicart';

    constructor() {
        this.extendProperties();
        this.initializeEventListeners();
        this.initializeActions();
    }

    extendProperties() {}

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
            await Blocks.updateHtml([MiniCart.MINICART_BLOCK_ID]);
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

        PageMessage.clearAll();
        requestAnimationFrame(() => {
            document.body.classList.add(this.classes.cartOpen);
        });
    }

    close() {
        document.body.classList.remove(this.classes.cartOpen);
    }

    async removeProduct(trigger) {
        const itemId = trigger.getAttribute('data-item-id');
        if (!itemId) return;

        try {
            await Request.post(MiniCart.CART_REMOVE_URL, { item_id: itemId });
            this.afterRemoveProduct(itemId);
        } catch (error) {
            console.error(`Error removing product (ID: ${itemId}) from minicart:`, error);
        }
    }

    afterRemoveProduct(itemId) {
        const itemToRemove = $(`[data-item-id="${itemId}"]`, $(this.component))?.closest('li');
        if (!itemToRemove) return;

        itemToRemove.addEventListener('animationend', () => this.refresh(), { once: true });
        this.fadeOutItem(itemToRemove);
    }

    fadeOutItem(item) {
        item.classList.add(this.classes.fade);
    }
}
