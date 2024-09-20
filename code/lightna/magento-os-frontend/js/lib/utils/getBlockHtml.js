import request from '../HttpClient';

export function getBlockHtml(blockId, data = {}) {
    data.blockId = blockId;
    data.entityType = pageContext.entity.type;
    data.entityId = pageContext.entity.id;

    return request.post('/lightna/block', data);
}
