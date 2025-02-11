import { UserInput } from 'lightna/lightna-engine/lib/UserInput';
import { Request } from 'lightna/lightna-engine/lib/Request';
import { ClickEventDelegator} from 'lightna/magento-frontend/common/ClickEventDelegator';

export class AddToCart {
    addToCartUrl = '/checkout/cart/add';
    classes = {
        loading: 'loading',
        disabled: 'btn-disabled',
    };
    actions = {
        click: {
            'add-to-cart': [(event, trigger) => this.onAddProduct(trigger)],
        }
    }

    constructor() {
        this.component = '.cjs-add-to-cart';
        this.initializeActions();
    }

    initializeActions() {
        ClickEventDelegator.add(this.actions.click);
    }

    async onAddProduct(trigger) {
        const component = trigger.closest(this.component);
        if (!component) return;

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

        const response = await Request.post(this.addToCartUrl, data);
        this.addProductSuccess(response);
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
