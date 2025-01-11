import { PageMessage} from 'lightna/magento-frontend/common/PageMessage';

export function extend(Request) {
    return class extends Request {
        static _onSuccess(response) {
           super._onSuccess(response);

           if (!response.messagesHtml) {
               return;
           }

           new PageMessage(response.messagesHtml);
        }
    }
}
