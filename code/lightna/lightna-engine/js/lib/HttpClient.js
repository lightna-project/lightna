import { FormKey } from 'lightna/lightna-engine/lib/FormKey';
import { objectToQuery } from 'lightna/lightna-engine/lib/utils/objectToQuery';

class HttpClient {
    headers = {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Request-With': 'Lightna',
    };
    lock = false;

    constructor(options = {}) {
        this.headers = { ...this.headers, ...options.headers };
    }

    async fetch(url, options = {}) {
        if (options.top && this.lock) {
            throw new Error(
                `HttpClient: Can't send new top level request for "${url}" until no response from previous`,
            );
        }

        const onSuccess = options.onSuccess ? options.onSuccess : this.onSuccess;
        const onError = options.onError ? options.onError : this.onError;
        const headers = { ...this.headers, ...options.headers };

        try {
            const response = await fetch(url, {
                ...options,
                headers: headers,
            });

            this.lock = false;

            return await this.handleJson(response, onSuccess, onError);
        } catch (error) {
            this.lock = false;
            onError(error);
        }
    }

    async handleJson(response, onSuccess, onError) {
        if (response.headers.get('Content-Type') !== 'application/json') {
            throw new Error('JSON expected');
        }

        let responseJson;
        try {
            responseJson = await response.clone().json();
        } catch (e) {
            let text = await response.clone().text();
            throw new Error('Invalid response JSON received: "' + text + '"');
        }

        if (response.ok) {
            onSuccess(responseJson);
        } else {
            onError(responseJson);
        }

        if (responseJson.messagesHtml) {
            document.dispatchEvent(new CustomEvent('page-messages', {
                detail: {
                    messagesHtml: responseJson.messagesHtml
                },
            }));
        }

        return responseJson;
    }

    get(url, options = {}) {
        return this.fetch(url, {
            ...options,
            method: 'GET',
        });
    }

    post(url, data, options = {}) {
        if (!data.form_key) {
            data.form_key = FormKey.get();
        }

        return this.fetch(url, {
            ...options,
            body: objectToQuery(data),
            method: 'POST',
        });
    }

    onSuccess(response) {
    }

    onError(error) {
        throw error;
    }
}

const request = new HttpClient();

export default request;
