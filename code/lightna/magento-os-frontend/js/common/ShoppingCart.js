import request from '../lib/HttpClient';
import { $, $$, getBlockHtml } from '../lib/utils';

export class ShoppingCart {
    blockId = 'minicart';
    removeFromCartUrl = '/checkout/sidebar/removeItem';
    minActionDuration = 200;
    classes = {
        cartOpen: 'minicart-open',
        fade: 'fade-out',
    };

    constructor() {
        this.shoppingCart = '.cjs-minicart';
        this.bindEvents();
    }

    bindEvents() {
        document.addEventListener('add-to-cart', this.update.bind(this));
        $('[data-action="open-minicart"]').addEventListener('click', this.update.bind(this));
    }

    bindCartActionsEvents() {
        $$('[data-action="open-minicart"]').foreach((i, trigger) => {
            trigger.addEventListener('click', this.open.bind(this));
        });

        $$('[data-action="close-minicart"]').foreach((i, trigger) => {
            trigger.addEventListener('click', this.close.bind(this));
        });

        $$('[data-action="remove-product"]').foreach((i, trigger) => {
            const itemId = trigger.getAttribute('data-item-id');
            trigger.addEventListener('click', () => {
                this.removeProduct(itemId);
            });
        });
    }

    async update() {
        const minicartHtml = await getBlockHtml(this.blockId);
        this.redraw(minicartHtml);
        this.open();
    }

    redraw(html) {
        $(this.shoppingCart).outerHTML = html;
        this.bindCartActionsEvents();
    }

    open() {
        setTimeout(() => {
            document.body.classList.add(this.classes.cartOpen);
        }, this.minActionDuration);
    }

    close() {
        document.body.classList.remove(this.classes.cartOpen);
    }

    async removeProduct(itemId) {
        const data = {
            item_id: itemId,
        };
        const itemToRemove = $(
          `[data-item-id="${itemId}"]`,
          $(this.shoppingCart),
        ).closest('li');

        await request.post(this.removeFromCartUrl, data, {
            onSuccess: this.onProductRemove.bind(this, itemToRemove),
        });
    }

    onProductRemove(item) {
        item.classList.add(this.classes.fade);
        setTimeout(() => {
            this.update.bind(this)();
        }, this.minActionDuration);
    }
}
