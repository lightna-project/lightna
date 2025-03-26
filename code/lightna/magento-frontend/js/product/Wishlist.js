import { $$ } from 'lightna/engine/lib/utils/dom';
import { Blocks } from 'lightna/engine/lib/Blocks';
import { Request } from 'lightna/engine/lib/Request';

export class Wishlist {
    component = '.cjs-wishlist-button';
    BLOCK_ID = 'wishlist-button';

    constructor() {
        this.extendProperties();
        document.addEventListener('page-ready', this.initializeEventListeners.bind(this));
    }

    extendProperties() {
    }

    initializeEventListeners() {
        $$(this.component).forEach((component) => {
            component.addEventListener('click', (event) => this.onClick(event, component));
        });
    }

    async onClick(event, component) {
        // wishlist/index/remove/ item: 2
        await Request.post(
            '/wishlist/index/add',
            { product: component.dataset.productId },
        );
        await Blocks.updateHtml([this.BLOCK_ID]);
        this.initializeEventListeners();
    }
}
