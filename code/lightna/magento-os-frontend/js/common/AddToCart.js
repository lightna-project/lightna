import { Cookie } from '../lib/Cookie';
import { UserInput } from '../lib/UserInput';
import request from '../lib/HttpClient';
import { $, $$ } from "../lib/utils";
import { PageMessage } from './PageMessage';

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
        this.components.foreach((i, component) => {
            const actionTrigger = $('[data-action="add-to-cart"]', component);
            actionTrigger.addEventListener('click', () => {
                this.addProduct(component, actionTrigger);
                this.toggleAnimation(actionTrigger, true);
            });
        });
    }

    async addProduct(component, actionTrigger) {
        const data = {
            ...UserInput.collect(component),
            product: component.getAttribute('data-product-id'),
        };

        await request.post(this.addToCartUrl, data, {
            onSuccess: this.addProductSuccessHandler.bind(this),
            showMessage: false,
        })

        this.toggleAnimation(actionTrigger, false);
    }

    addProductSuccessHandler(response) {
        // todo: add type of message to response
        const temp = document.createElement('div');
        temp.innerHTML = response.messagesHtml;
        if (temp.querySelector('.message').classList.contains('success')) {
            document.dispatchEvent(new CustomEvent('add-to-cart'));
            this.updateMagentoCartSectionId();
        } else {
            this.showResponseMessage(response);
        }
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

    showResponseMessage(response) {
        new PageMessage(response.messagesHtml);
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
