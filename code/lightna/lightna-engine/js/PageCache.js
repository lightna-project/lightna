import { Blocks } from 'lightna/engine/lib/Blocks';
import { PageReadyEvent } from 'lightna/engine/PageReadyEvent';

export class PageCache {

    constructor() {
        this.extendProperties();
        this.handlePrivateBlocks().then(() => {
            PageReadyEvent.trigger();
        });
    }

    extendProperties() {
    }

    async handlePrivateBlocks() {
        let privateBlocks = pageContext.privateBlocks;
        if (!privateBlocks.length) {
            return;
        }

        await Blocks.updateHtml(
            privateBlocks,
            { 'renderLazyBlocks': false },
        );
    }
}
