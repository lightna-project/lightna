import { PageMessage} from 'lightna/magento-os-frontend/common/PageMessage';

export function extend(HttpClient) {
    return class extends HttpClient {
        static _onSuccess(response) {
           super._onSuccess(response);

           if (!response.messagesHtml) {
               return;
           }

           new PageMessage(response.messagesHtml);
        }
    }
}
