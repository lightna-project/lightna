import { Blocks } from 'lightna/engine/lib/Blocks';
import { pageReady } from "lightna/engine/PageReady";

export class PageCache {

    constructor() {
        this.extendProperties();
        this.handlePrivateBlocks().then(() => {
            pageReady.trigger();
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
