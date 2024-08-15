import { Cookie } from '../lib/Cookie';
import { UserInput } from '../lib/UserInput';
import request from '../lib/HttpClient';
import { $, $$ } from '../lib/utils';
import { PageMessage } from './PageMessage';

export class ShoppingCart {
    minicart;

    constructor() {
        $$('.cjs-add-to-cart').foreach((i, component) => {
            const actionTrigger = $('button', component);
            actionTrigger.addEventListener('click', () => {
                this.addProduct(component, actionTrigger);
                this.toggleAnimation(actionTrigger, true);
            });
        });

        this.minicart = $('.cjs-minicart');
        this.minicart.addEventListener('click', this.openCart.bind(this));
        this.addCartEventListeners();
    }

    addCartEventListeners() {
        $$('.cjs-remove-from-cart').foreach((i, removeButton) => {
            const itemId = removeButton.getAttribute('data-item-id');
            removeButton.addEventListener('click', () => {
                this.removeProduct(itemId);
            });
        });

        $$('.cjs-close-minicart').foreach((i, trigger) => {
            trigger.addEventListener('click', this.closeCart.bind(this));
        });
    }

    addProduct(component, actionTrigger) {
        const data = UserInput.collect(component);
        data.product = component.getAttribute('data-product-id');
        request.post('/checkout/cart/add', data, {
            onSuccess: (response) => {
                this.toggleAnimation(actionTrigger, false);
                this.updateMinicart.bind(this)();
                // todo: refactor this; add type of message to response
                const temp = document.createElement('div');
                temp.innerHTML = response.messagesHtml;
                if (temp.querySelector('.message').classList.contains('success')) {
                    setTimeout(() => {
                        this.openCart();
                    }, 300);
                } else {
                    new PageMessage(response.messagesHtml);
                }
            },
            showMessage: false,
        });
    }

    removeProduct(itemId) {
        const data = {
            item_id: itemId,
        };
        const itemToRemove = $(`[data-item-id="${itemId}"]`, $('.cjs-minicart-content')).closest('li');

        request.post('/checkout/sidebar/removeItem/', data, {
            onSuccess: () => {
                itemToRemove.classList.add('fade-out');
                setTimeout(() => {
                    this.updateMinicart.bind(this)();
                }, 200);
            },
        });
    }

    updateMinicart() {
        const updateMinicartUrl = document.location.pathname + '?block=.body.minicart-content';

        request
            .post(
                updateMinicartUrl,
                {},
                {
                    onSuccess: this.updateMinicartHtml.bind(this),
                },
            )
            .then(() => {
                this.updateMagentoCartSectionId();
            });
    }

    updateMinicartHtml(html) {
        $('.cjs-minicart-content').outerHTML = html;
        this.addCartEventListeners();
        this.updateCounter();
    }

    updateMagentoCartSectionId() {
        let sids = Cookie.get('section_data_ids');
        sids = sids ? JSON.parse(decodeURIComponent(sids)) : {};
        sids.cart = sids.cart ? sids.cart + 1000 : 1;

        Cookie.set('section_data_ids', encodeURIComponent(JSON.stringify(sids)));
    }

    updateCounter() {
        $('.cjs-minicart-counter').innerText = $('.minicart').dataset.totalQty;
    }

    openCart() {
        document.body.classList.add('minicart-open');
    }

    closeCart() {
        document.body.classList.remove('minicart-open');
    }

    toggleAnimation(element, isLoading) {
        element.classList.toggle('loading', isLoading);
        element.classList.toggle('btn-disabled', isLoading);
    }
}
