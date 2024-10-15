import { Blocks } from 'lightna/lightna-engine/lib/Blocks';

export class Fpc {

    constructor() {
        this.handlePrivateBlocks();
    }

    async handlePrivateBlocks() {
        let privateBlocks = pageContext.privateBlocks;
        if (!privateBlocks.length) {
            return;
        }

        await Blocks.updateHtml(privateBlocks);
    }
}
