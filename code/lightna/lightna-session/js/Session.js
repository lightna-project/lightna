import { $ } from 'lightna/engine/lib/utils/dom';
import { Request } from 'lightna/engine/lib/Request';
import { PageReadyEvent } from 'lightna/engine/PageReadyEvent';

export class Session {

    constructor() {
        this.extendProperties();
        this.initializeEventListeners();
    }

    extendProperties() {
    }

    initializeEventListeners() {
        PageReadyEvent.addListener(this.onPageReady.bind(this));
    }

    onPageReady() {
        if ($('#session-isReindexRequired')) {
            Request.post('/lightna/session/update');
        }
    }
}
