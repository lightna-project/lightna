import { Cookie } from '../lib/Cookie';
import { UserInput } from '../lib/UserInput';
import request from '../lib/HttpClient';
import { $, $$ } from "../lib/utils/dom";

export class AddToCart {
    components = [];
    addToCartUrl = '/checkout/cart/add';
    classes = {
        loading: 'loading',
        disabled: 'btn-disabled',
    };

    constructor() {
        this.components = $$('.cjs-add-to-cart');
        this.bindEvents();
    }

    bindEvents() {
        this.components.forEach((component) => {
            const actionTrigger = $('[data-action="add-to-cart"]', component);
            actionTrigger.addEventListener('click', () => {
                this.addProduct(component, actionTrigger);
                this.toggleAnimation(actionTrigger, true);
            });
        });
    }

    async addProduct(component, actionTrigger) {
        console.log('addProduct called from AddToCart.js');
        const data = {
            ...UserInput.collect(component),
            product: component.getAttribute('data-product-id'),
            noSuccessMessages: true,
        };

        await request.post(this.addToCartUrl, data, {
            onSuccess: this.onAddProductSuccess.bind(this)
        })

        this.toggleAnimation(actionTrigger, false);
    }

    onAddProductSuccess(response) {
        if (response.messagesHtml) {
            return;
        }
        document.dispatchEvent(new CustomEvent('add-to-cart'));
        this.updateMagentoCartSectionId();
    }

    updateMagentoCartSectionId() {
        let sids = Cookie.get('section_data_ids');
        sids = sids ? JSON.parse(decodeURIComponent(sids)) : {};
        sids.cart = sids.cart ? sids.cart + 1000 : 1;

        Cookie.set(
            'section_data_ids',
            encodeURIComponent(JSON.stringify(sids)),
        );
    }

    toggleAnimation(element, isLoading) {
        element.classList.toggle(this.classes.loading, isLoading);
        element.classList.toggle(this.classes.disabled, isLoading);
    }

    reload() {
        this.components = $$('.cjs-add-to-cart');
        this.bindEvents();
    }
}
