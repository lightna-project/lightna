import { Blocks } from 'lightna/engine/lib/Blocks';
import { ClickEventDelegator } from 'lightna/magento-frontend/common/ClickEventDelegator';
import { Request } from 'lightna/engine/lib/Request';
import { pageReady } from "lightna/engine/PageReady";

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
        pageReady.addListener(this.initializeActions.bind(this));
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
