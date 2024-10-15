import { Cookie } from 'lightna/lightna-engine/lib/Cookie';
import { randomString } from 'lightna/lightna-engine/lib/utils/randomString';

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
