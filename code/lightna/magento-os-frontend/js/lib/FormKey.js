import { Cookie } from './Cookie';
import { randomString } from './utils/randomString';

export class FormKey {
    static get() {
        const value = this.getFormKeyCookie();
        if (value) {
            return value;
        }
        this.create();

        return this.getFormKeyCookie();
    }

    static getFormKeyCookie() {
        return Cookie.get('form_key');
    }

    static create() {
        Cookie.set('form_key', randomString(16), 1);
    }
}
