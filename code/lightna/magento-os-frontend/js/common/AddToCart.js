import { UserInput } from 'lightna/lightna-engine/lib/UserInput';
import { HttpClient } from 'lightna/lightna-engine/lib/HttpClient';
import { $ } from 'lightna/lightna-engine/lib/utils/dom';

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
                this.onAddProduct(component).then();
            }
        });
    }

    getAddToCartTrigger(element) {
        return element.closest(this.trigger);
    }

    async onAddProduct(component) {
        this.beforeAddProduct(component);
        await this.addProduct(component);
        this.afterAddProduct(component);
    }

    async addProduct(component) {
        const data = {
            ...UserInput.collect(component),
            product: component.dataset.productId,
            noSuccessMessages: true,
        };

        await HttpClient.post(this.addToCartUrl, data, {
            onSuccess: this.addProductSuccess.bind(this)
        })
    }

    beforeAddProduct(component) {
        this.toggleAnimation($(this.trigger, component), true);
    }

    addProductSuccess(response) {
        if (response.messagesHtml) {
            return;
        }
        document.dispatchEvent(new CustomEvent('add-to-cart'));
    }

    afterAddProduct(component) {
        this.toggleAnimation($(this.trigger, component), false);
    }

    toggleAnimation(element, isLoading) {
        element.classList.toggle(this.classes.loading, isLoading);
        element.classList.toggle(this.classes.disabled, isLoading);
    }
}
