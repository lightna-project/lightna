import { UserInput } from '../lib/UserInput';
import request from '../lib/HttpClient';
import { $ } from "../lib/utils/dom";

export class AddToCart {
    addToCartUrl = '/checkout/cart/add';
    classes = {
        loading: 'loading',
        disabled: 'btn-disabled',
    };

    constructor() {
        this.component = '.cjs-add-to-cart';
        this.trigger = '[data-action="add-to-cart"]';
        this.bindEvents();
    }

    bindEvents() {
        $('body').addEventListener('click', (event) => {
            const trigger = this.getAddToCartTrigger(event.target);
            if (trigger) {
                const component = trigger.closest(this.component);
                this.beforeAddProduct(trigger);
                this.addProduct(component)
                    .then(this.afterAddProduct.bind(this, trigger));
            }
        });
    }

    getAddToCartTrigger(element) {
        return element.closest(this.trigger);
    }

    async addProduct(component) {
        const data = {
            ...UserInput.collect(component),
            product: component.dataset.productId,
            noSuccessMessages: true,
        };

        await request.post(this.addToCartUrl, data, {
            onSuccess: this.addProductSuccess.bind(this)
        })
    }

    beforeAddProduct(trigger) {
        this.toggleAnimation(trigger, true);
    }

    addProductSuccess(response) {
        if (response.messagesHtml) {
            return;
        }
        document.dispatchEvent(new CustomEvent('add-to-cart'));
    }

    afterAddProduct(trigger) {
        this.toggleAnimation(trigger, false);
    }

    toggleAnimation(element, isLoading) {
        element.classList.toggle(this.classes.loading, isLoading);
        element.classList.toggle(this.classes.disabled, isLoading);
    }
}
