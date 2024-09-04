import request from './HttpClient';

export function $(element, scope) {
    // if elements is a string then it's selector
    if (getClassName(element) === 'String') {
        if (!scope) scope = document;
        return scope.querySelector(element);
    }

    return element;
}

export function $$(elements, scope = document) {
    if (scope === null) return [];

    // if elements is a string then it's selector
    if (getClassName(elements) === 'String') {
        elements = scope.querySelectorAll(elements);
    }

    if (getClassName(elements) === 'NodeList') {
        let nodeList = elements;
        elements = [];
        for (let i = 0; i < nodeList.length; i++) elements.push(nodeList[i]);
    }

    // if single element received
    if (getClassName(elements) !== 'Array') elements = [elements];

    return elements;
}

export function getClassName(object) {
    if (object === null) return null;
    let name = object.constructor.name;
    if (name) return name;

    // firefox %(
    return object.constructor.toString().match(/function\s+(\w*)/)[1];
}

export function randomString(length) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    length = typeof length === 'undefined' ? 16 : length;

    let string = '';
    for (let i = 0; i < length; i++) {
        string += chars.charAt(Math.floor(Math.random() * chars.length));
    }

    return string;
}

export function isElementInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

export function objectToQuery(data, prefix) {
    let q = [],
        p;
    for (p in data) {
        if (data.hasOwnProperty(p)) {
            const k = prefix ? prefix + '[' + p + ']' : p;
            const v = data[p];
            q.push(
                v !== null && typeof v === 'object'
                    ? objectToQuery(v, k)
                    : encodeURIComponent(k) + '=' + encodeURIComponent(v),
            );
        }
    }
    return q.join('&');
}

export function isTouchDevice() {
    return 'ontouchstart' in window || navigator.maxTouchPoints > 0 || navigator.msMaxTouchPoints > 0;
}

export function getBlockHtml(path, data = {}) {
    const url = `${document.location.pathname}?block=${path}`;

    return request.post(url, data);
}

Object.prototype.foreach = function (cb) {
    for (let i in this) {
        if (!this.hasOwnProperty(i)) continue;
        if (cb(i, this[i]) === false) break;
    }
};
