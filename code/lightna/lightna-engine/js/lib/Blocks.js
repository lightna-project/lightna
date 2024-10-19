import { $ } from 'lightna/lightna-engine/lib/utils/dom';
import { HttpClient } from 'lightna/lightna-engine/lib/HttpClient';

export class Blocks {

    static async getHtml(blockIds, data = {}) {
        data.blockIds = blockIds;
        data.entityType = pageContext.entity.type;
        data.entityId = pageContext.entity.id;

        return HttpClient.post('/lightna/block', data);
    }

    static async updateHtml(blockIds = [], data = []) {
        let blocks = await this.getHtml(blockIds, data);
        this._renderBlocks(blocks);
    }

    static _renderBlocks(blocks) {
        Object.keys(blocks).forEach((id) => {
            this._updateBlockHtml(id, blocks[id])
        });
    }

    static _updateBlockHtml(id, html) {
        let hook = this._getBlockHook(id);
        let block = hook.nextSibling;

        block.outerHTML = html;
        hook.remove();
    }

    static _getBlockHook(id) {
        return $('#block_hook_' + id);
    }
}
