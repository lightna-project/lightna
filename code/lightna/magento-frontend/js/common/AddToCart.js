import { UserInput } from 'lightna/lightna-engine/lib/UserInput';
import { Request } from 'lightna/lightna-engine/lib/Request';
import { PageMessage } from 'lightna/magento-frontend/common/PageMessage';
import { Blocks } from 'lightna/lightna-engine/lib/Blocks';
import { $ } from 'lightna/lightna-engine/lib/utils/dom';

export class AddToCart {
    addToCartUrl = '/checkout/cart/add';
    classes = {
        loading: 'loading',
        disabled: 'btn-disabled',
    };
    actions = {
        'add-to-cart': (component, trigger) => this.onAddProduct(component, trigger),
    }

    constructor() {
        this.component = '.cjs-add-to-cart';
        this.initializeEventListeners();
    }

    initializeEventListeners() {
        $('body').addEventListener('click', (event) => {
            if (!event.target.closest(this.component)) return;
            this.handleAddToCartActions(event);
        })
    }

    handleAddToCartActions(event) {
        const trigger = event.target.closest('[data-action]');
        if (!trigger) return;

        const action = trigger.getAttribute('data-action');
        const component = trigger.closest(this.component);
        const handler = this.actions[action];

        if (handler) {
            try {
                handler(component, trigger);
            } catch (error) {
                console.error(`Error handling add-to-cart action: ${action}`, error);
            }
        }
    }

    async onAddProduct(component, trigger) {
        this.beforeAddProduct(component, trigger);
        try {
            await this.addProduct(component);
        } finally {
            this.afterAddProduct(component, trigger);
        }
    }

    async addProduct(component) {
        const data = {
            ...UserInput.collect(component),
            product: component.dataset.productId,
            noSuccessMessages: true,
        };

        try {
            const response = await Request.post(this.addToCartUrl, data);
            this.addProductSuccess(response);
        } catch (error) {
            console.error('Failed to add product to cart:', error);
        }
    }

    beforeAddProduct(component, trigger) {
        this.toggleAnimation(trigger, true);
    }

    addProductSuccess(response) {
        document.dispatchEvent(new CustomEvent('add-to-cart', {
            detail: { response }
        }));
    }

    afterAddProduct(component, trigger) {
        this.toggleAnimation(trigger, false);
    }

    toggleAnimation(element, isLoading) {
        element.classList.toggle(this.classes.loading, isLoading);
        element.classList.toggle(this.classes.disabled, isLoading);
    }
}
