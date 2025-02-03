import { FormKey } from 'lightna/lightna-engine/lib/FormKey';
import { objectToQuery } from 'lightna/lightna-engine/lib/utils/objectToQuery';

export class Request {
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
        data ??= {};

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
                `Request: Can't send new top level request for "${url}" until no response from previous`,
            );
        }
        options.headers = { ...this.headers, ...options.headers };

        try {
            this._lock = fetch(url, {
                ...options,
                headers: options.headers,
            });

            const response = await this._lock;
            this._lock = null;

            if (response.status === 503) {
                if (confirm('The service is under maintenance. Reload the page?')) {
                    document.location.reload()
                } else {
                    throw new Error('The service is under maintenance. Terminated.');
                }
            }

            return await this._handleJson(response);
        } finally {
            this._lock = null;
        }
    }

    static async _handleJson(response) {
        if (response.headers.get('Content-Type') !== 'application/json') {
            throw new Error('JSON expected');
        }

        let responseJson;
        try {
            responseJson = await response.clone().json();
        } catch (e) {
            let text = await response.clone().text();
            throw new Error(`Invalid response JSON received: "${text}"`);
        }

        if (response.ok) {
            this._onSuccess(responseJson);
        } else {
            this._onError(responseJson);
        }

        return responseJson;
    }

    static _onSuccess(response) {
        // Extension point
    }

    static _onError(response) {
        // Extension point
    }
}
