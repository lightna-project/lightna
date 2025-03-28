import { Request } from 'lightna/engine/lib/Request';
import { $ } from 'lightna/engine/lib/utils/dom';
import { resolveAnimationEnd } from 'lightna/engine/lib/utils/resolveAnimationEnd';
import { Blocks } from 'lightna/engine/lib/Blocks';
import { PageMessage } from 'lightna/magento-frontend/common/PageMessage';
import { ClickEventDelegator} from 'lightna/magento-frontend/common/ClickEventDelegator';

export class MiniCart {
    static BLOCK_ID = 'minicart';
    static URL_REMOVE_ITEM = '/checkout/sidebar/removeItem';
    classes = {
        cartOpen: 'minicart-open',
        fade: 'fade-out',
        disableClick: 'pointer-events-none',
    };
    actions = {
        click: {
            'open-minicart': [() => this.open()],
            'close-minicart': [() => this.close()],
            'remove-product': [(event, target) => this.onRemoveProduct(target)],
        }
    };
    component = '.cjs-minicart';

    constructor() {
        this.extendProperties();
        this.initializeEventListeners();
        this.initializeActions();
    }

    extendProperties() {
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
            await Blocks.updateHtml([MiniCart.BLOCK_ID]);
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

    async onRemoveProduct(element) {
        await this.removeProduct(element.dataset.itemId);
        await this.animateRemoval(element);
        await this.refresh();
    }

    async removeProduct(itemId) {
        const response = await Request.post(MiniCart.URL_REMOVE_ITEM, {
            item_id: itemId,
        });
        if (!response.success) {
            throw new Error(
                `Error removing product (ID: ${itemId}) from minicart: ${response.error_message}`,
            );
        }
    }

    async animateRemoval(element) {
        const item = this.getItemElement(element);
        if (!item) return;

        item.classList.add(this.classes.disableClick);
        item.classList.add(this.classes.fade);
        await resolveAnimationEnd(item);
    }

    getItemElement(element) {
        return element.closest('li');
    }
}
