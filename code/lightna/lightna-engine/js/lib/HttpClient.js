import { FormKey } from 'lightna/lightna-engine/lib/FormKey';
import { objectToQuery } from 'lightna/lightna-engine/lib/utils/objectToQuery';

export class HttpClient {
    static headers = {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Request-With': 'Lightna',
    };
    static _lock = null;

    static get(url, options = {}) {
        return this.fetch(url, {
            ...options,
            method: 'GET',
        });
    }

    static post(url, data, options = {}) {
        if (!data.form_key) {
            data.form_key = FormKey.get();
        }

        return this.fetch(url, {
            ...options,
            body: objectToQuery(data),
            method: 'POST',
        });
    }

    static async fetch(url, options = {}) {
        if (options.top && this._lock) {
            throw new Error(
                `HttpClient: Can't send new top level request for "${url}" until no response from previous`,
            );
        }

        const onSuccess = options.onSuccess ? options.onSuccess : this._onSuccess;
        const onError = options.onError ? options.onError : this._onError;
        options.headers = { ...this.headers, ...options.headers };

        try {
            this._lock = fetch(url, {
                ...options,
                headers: options.headers,
            });

            const response = await this._lock;
            this._lock = null;

            return await this._handleJson(response, onSuccess, onError);
        } catch (error) {
            this._lock = null;
            onError(error);
        }
    }

    static async _handleJson(response, onSuccess, onError) {
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

    static _onSuccess(response) {
    }

    static _onError(error) {
        throw error;
    }
}
