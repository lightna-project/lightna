import { Blocks } from 'lightna/engine/lib/Blocks';
import { Request } from 'lightna/engine/lib/Request';
import { ClickEventDelegator} from 'lightna/magento-frontend/common/ClickEventDelegator';

export class Wishlist {
    static WISHLIST_ADD_URL = '/wishlist/index/add';
    static WISHLIST_REMOVE_URL = 'wishlist/index/remove/';
    static WISHLIST_BLOCK_ID = 'wishlist-button';
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
            Wishlist.WISHLIST_ADD_URL,
            { product: component.dataset.productId },
        );
        await Blocks.updateHtml([Wishlist.WISHLIST_BLOCK_ID]);
    }

    async removeProduct(component) {
        this.animateButton(component);
        await Request.post(
            Wishlist.WISHLIST_REMOVE_URL,
            { item: component.dataset.itemId },
        );
        await Blocks.updateHtml([Wishlist.WISHLIST_BLOCK_ID]);
    }

    animateButton(button) {
        button.classList.add(this.classes.animated);
    }
}
