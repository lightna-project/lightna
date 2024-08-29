import { FormKey } from './FormKey';
import { PageMessage } from '../common/PageMessage';
import { objectToQuery } from './utils';

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

            if (response.headers.get('Content-Type') === 'application/json') {
                return await this.handleJson(response, onSuccess, onError);
            } else {
                return await this.handleText(response, onSuccess, onError);
            }
        } catch (error) {
            this.lock = false;
            onError(error);
        }
    }

    async handleJson(response, onSuccess, onError) {
        const responseJson = await response.json();

        if (response.ok) {
            onSuccess(responseJson);
        } else {
            onError(responseJson);
        }

        if (responseJson.messagesHtml) {
            new PageMessage(responseJson.messagesHtml);
        }

        return responseJson;
    }

    async handleText(response, onSuccess, onError) {
        const responseText = await response.text();

        if (response.ok) {
            onSuccess(responseText);
        } else {
            onError(responseText);
        }

        return responseText;
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

    onSuccess(response) {}

    onError(error) {}
}

const request = new HttpClient();

export default request;
