import {AddToCart as Base} from '../../magento-os-frontend/js/common/AddToCart';

export class AddToCart extends Base {
    async addProduct(component) {
        await super.addProduct(component);
        console.log('addProduct called from extend-js-here-and-here/AddToCartExtend.js');
    }
}