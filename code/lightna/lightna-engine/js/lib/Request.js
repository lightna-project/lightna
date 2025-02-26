import { FormKey } from 'lightna/engine/lib/FormKey';
import { objectToQuery } from 'lightna/engine/lib/utils/objectToQuery';

export class Request {
    static headers = {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Request-With': 'Lightna',
    };
    static _lock = new Map();

    static get(url, options = {}) {
        return this._send(url, { ...options, method: 'GET' });
    }

    static post(url, data = {}, options = {}) {
        data.form_key ??= FormKey.get();
        return this._send(url, { ...options, body: objectToQuery(data), method: 'POST' });
    }

    static async _send(url, options) {
        this._checkLock(url, options);
        options.headers = { ...this.headers, ...options.headers };

        try {
            const response = await this._performRequest(url, options);
            this._handleMaintenance(response);
            return this._processResponse(response);
        } finally {
            this._lock.delete(url);
        }
    }

    static _checkLock(url, options) {
        const isTop = options.top ?? true;
        if (isTop && this._lock.has(url)) {
            throw new Error(`Request: Can't send new top-level request for "${url}" until the previous one completes.`);
        }
    }

    static async _performRequest(url, options) {
        const requestPromise = fetch(url, options);
        this._lock.set(url, requestPromise);
        return await requestPromise;
    }

    static async _processResponse(response) {
        const json = await this._parseJson(response);
        response.ok ? this._onSuccess(json) : this._onError(json);
        return json;
    }

    static _handleMaintenance(response) {
        if (response.status === 503) {
            if (confirm('The service is under maintenance. Reload the page?')) {
                document.location.reload();
            }
            throw new Error('The service is under maintenance. Terminated.');
        }
    }

    static async _parseJson(response) {
        if (response.headers.get('Content-Type') !== 'application/json') {
            throw new Error('Expected JSON response');
        }

        try {
            return await response.clone().json();
        } catch (e) {
            const text = await response.clone().text();
            throw new Error(`Invalid JSON received: "${text}"`);
        }
    }

    static _onSuccess(response) {
        // Extension point
    }

    static _onError(response) {
        // Extension point
    }
}
