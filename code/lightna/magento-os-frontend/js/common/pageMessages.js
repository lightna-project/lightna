import { PageMessage} from 'lightna/magento-os-frontend/common/PageMessage';

document.addEventListener('page-messages', (event) => {
    new PageMessage(event.detail.messagesHtml);
})
