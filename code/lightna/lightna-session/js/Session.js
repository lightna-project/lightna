import { $ } from 'lightna/engine/lib/utils/dom';
import { Request } from 'lightna/engine/lib/Request';

export class Session {

    constructor() {
        this.extendProperties();
        this.initializeEventListeners();
    }

    extendProperties() {
    }

    initializeEventListeners() {
        document.addEventListener('page-ready', this.onPageReady);
    }

    onPageReady() {
        if ($('#session-isReindexRequired')) {
            Request.post('/lightna/session/update');
        }
    }
}
