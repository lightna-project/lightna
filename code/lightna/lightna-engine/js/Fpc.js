import { Blocks } from 'lightna/lightna-engine/lib/Blocks';

export class Fpc {

    constructor() {
        this.handlePrivateBlocks().then(() => {
            document.dispatchEvent(new CustomEvent('page-ready'));
        });
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
