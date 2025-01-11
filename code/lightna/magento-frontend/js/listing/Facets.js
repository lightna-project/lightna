import { $, $$ } from 'lightna/lightna-engine/lib/utils/dom';

export class Facets {
    constructor() {
        this.jsc = $('.cjs-facets');
        this.bindEvents();
    }

    bindEvents() {
        $$('input[type=checkbox]', this.jsc).forEach((element) => {
            element.addEventListener('change', () => {
                this.onChange(element);
            });
        });
    }

    onChange(element) {
        let query = this.getUpdatedQuery(element);
        this.applyQuery(query);
    }

    getUpdatedQuery(element) {
        let query = new URLSearchParams(location.search);
        let qValue = query.get(element.name) ?? '';
        qValue = qValue !== '' ? qValue.split('_') : [];

        element.checked ? qValue.push(element.value) : qValue.splice(qValue.indexOf(element.value), 1);
        qValue.sort((a, b) => a - b);

        if (qValue.length) {
            query.set(element.name, qValue.join('_'));
        } else {
            query.delete(element.name);
        }

        return query;
    }

    applyQuery(query) {
        let search = query.toString();
        if (search) {
            location.search = search;
        } else {
            location = location.href.split('?')[0];
        }
    }
}
