import { $ } from 'lightna/engine/lib/utils/dom';
import { Request } from 'lightna/engine/lib/Request';

export class Blocks {

    static async getHtml(blockIds, data = {}) {
        const { type: entityType, id: entityId } = pageContext.entity;
        const requestData = {
            ...data,
            blockIds,
            entityType,
            entityId
        };
        const requestOptions = { top: false };

        return Request.post('/lightna/block', requestData, requestOptions);
    }

    static async updateHtml(blockIds = [], data = {}) {
        let blocks = await this.getHtml(blockIds, data);
        this._renderBlocks(blocks);
    }

    static _renderBlocks(blocks) {
        Object.keys(blocks).forEach((id) => {
            this._updateBlockHtml(id, blocks[id])
        });
    }

    static _updateBlockHtml(id, html) {
        $(`#block-wrapper-${id}`).outerHTML = html;
    }
}
