import { $ } from 'lightna/engine/lib/utils/dom';
import { Request } from 'lightna/engine/lib/Request';
import { pageReady } from "lightna/engine/PageReady";

export class Session {

    constructor() {
        this.extendProperties();
        this.initializeEventListeners();
    }

    extendProperties() {
    }

    initializeEventListeners() {
        pageReady.addListener(this.onPageReady.bind(this));
    }

    onPageReady() {
        if ($('#session-isReindexRequired')) {
            Request.post('/lightna/session/update');
        }
    }
}
