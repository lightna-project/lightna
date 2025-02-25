import { UserInput } from 'lightna/engine/lib/UserInput';
import { Request } from 'lightna/engine/lib/Request';
import { ClickEventDelegator} from 'lightna/magento-frontend/common/ClickEventDelegator';

export class AddToCart {
    static CART_ADD_URL= '/checkout/cart/add';
    classes = {
        loading: 'loading',
        disabled: 'btn-disabled',
    };
    actions = {
        click: {
            'add-to-cart': [(event, trigger) => this.onAddProduct(trigger)],
        }
    }
    component = '.cjs-add-to-cart';

    constructor() {
        this.extendProperties();
        this.initializeActions();
    }

    extendProperties() {}

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
        const response = await Request.post(AddToCart.CART_ADD_URL, this.collectData(component));
        this.addProductSuccess(response);
    }

    collectData(component) {
        return {
            ...UserInput.collect(component),
            product: component.dataset.productId,
            noSuccessMessages: true,
        };
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
