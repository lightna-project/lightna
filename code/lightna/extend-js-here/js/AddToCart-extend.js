import {AddToCart} from '../../magento-os-frontend/js/common/AddToCart';

const methodProto = AddToCart.prototype.addProduct;

AddToCart.prototype.addProduct = async function (component, actionTrigger) {
    methodProto.bind(this, component, actionTrigger)();
    console.log('addProduct called from extend-js-here/AddToCart-extend.js');
}
