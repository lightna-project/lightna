import { Request } from 'lightna/lightna-engine/lib/Request';
import { $, $$ } from 'lightna/lightna-engine/lib/utils/dom';
import { Blocks } from 'lightna/lightna-engine/lib/Blocks';
import { PageMessage } from 'lightna/magento-os-frontend/common/PageMessage';
import { PageOverlay } from 'lightna/magento-os-frontend/common/PageOverlay';

export class ShoppingCart {
    blockId = 'minicart';
    removeFromCartUrl = '/checkout/sidebar/removeItem';
    minActionDuration = 200;
    classes = {
        fade: 'fade-out',
    };
    overlay = null;

    constructor() {
        this.shoppingCart = '.cjs-minicart';
        this.bindEvents();
    }

    bindEvents() {
        document.addEventListener('add-to-cart', this.update.bind(this));
        $('body').addEventListener('click', this.onBodyClick.bind(this));
    }

    onBodyClick(event) {
        if (this.isOpenMinicartClick(event)) {
            this.update(false);
        }
    }

    isOpenMinicartClick(event) {
        return event.target.closest('[data-action="open-minicart"]');
    }

    bindCartActionsEvents() {
        if (this.getContentElement().areEventsBound) {
            return;
        }

        $$('[data-action="close-minicart"]').forEach((trigger) => {
            trigger.addEventListener('click', this.close.bind(this));
        });

        $$('[data-action="remove-product"]').forEach((trigger) => {
            const itemId = trigger.getAttribute('data-item-id');
            trigger.addEventListener('click', () => {
                this.removeProduct(itemId);
            });
        });

        this.getContentElement().areEventsBound = true;
    }

    getContentElement() {
        return $('#minicart-content');
    }

    async update(forceReload = true) {
        await this.loadContent(forceReload);
        this.open();
    }

    async loadContent(forceReload = true) {
        const reload = forceReload || !this.getContentElement();
        reload && await Blocks.updateHtml([this.blockId]);
        this.bindCartActionsEvents();
    }

    open() {
        PageMessage.clearAll();
        if (!this.overlay) {
            this.overlay = new PageOverlay('.cjs-minicart', 'minicart');
        }
        setTimeout(() => {
            this.overlay.show();
        }, this.minActionDuration);
    }

    close() {
        this.overlay.hide();
    }

    async removeProduct(itemId) {
        const data = {
            item_id: itemId,
        };
        const itemToRemove = $(
            `[data-item-id="${itemId}"]`,
            $(this.shoppingCart),
        ).closest('li');

        await Request.post(this.removeFromCartUrl, data).then(
            this.onProductRemove.bind(this, itemToRemove)
        );
    }

    onProductRemove(item) {
        item.classList.add(this.classes.fade);
        setTimeout(() => {
            this.update.bind(this)();
        }, this.minActionDuration);
    }
}
