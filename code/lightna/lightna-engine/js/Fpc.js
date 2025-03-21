import { Blocks } from 'lightna/engine/lib/Blocks';

export class Fpc {

    constructor() {
        this.extendProperties();
        this.handlePrivateBlocks().then(() => {
            document.dispatchEvent(new CustomEvent('page-ready'));
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
