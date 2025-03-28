import { Blocks } from 'lightna/engine/lib/Blocks';
import { Request } from 'lightna/engine/lib/Request';
import { ClickEventDelegator} from 'lightna/magento-frontend/common/ClickEventDelegator';

export class Wishlist {
    static URL_ADD = '/wishlist/index/add';
    static URL_REMOVE = 'wishlist/index/remove/';
    static BLOCK_ID = 'wishlist-button';
    classes = {
        animated: 'animated',
    };
    actions = {
        click: {
            'wishlist-add': [(event, component) => this.addProduct(component)],
            'wishlist-remove': [(event, component) => this.removeProduct(component)],
        },
    };

    constructor() {
        this.extendProperties();
        document.addEventListener('page-ready', () => this.initializeActions());
    }

    extendProperties() {
    }

    initializeActions() {
        ClickEventDelegator.add(this.actions.click);
    }

    async addProduct(component) {
        this.animateButton(component);
        await Request.post(
            Wishlist.URL_ADD,
            { product: component.dataset.productId },
        );
        await Blocks.updateHtml([Wishlist.BLOCK_ID]);
    }

    async removeProduct(component) {
        this.animateButton(component);
        await Request.post(
            Wishlist.URL_REMOVE,
            { item: component.dataset.itemId },
        );
        await Blocks.updateHtml([Wishlist.BLOCK_ID]);
    }

    animateButton(button) {
        button.classList.add(this.classes.animated);
    }
}
