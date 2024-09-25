import {AddToCart as Base} from '../../magento-os-frontend/js/common/AddToCart';

export class AddToCart extends Base {
    async addProduct(component, actionTrigger) {
        await super.addProduct(component, actionTrigger);
        console.log('addProduct called from extend-js-here/AddToCartExtend.js');
    }
}