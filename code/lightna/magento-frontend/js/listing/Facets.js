import { $ } from 'lightna/engine/lib/utils/dom';

export class Facets {
    component = '.cjs-facets';

    constructor() {
        this.extendProperties();
        this.initializeEventListeners();
    }

    extendProperties() {
    }

    initializeEventListeners() {
        $(this.component)?.addEventListener('change', (event) => {
            const element = event.target;
            if (element.type === 'checkbox') {
                this.onChange(element);
            }
        });
    }

    onChange(item) {
        const query = this.getUpdatedQuery(item);
        this.applyQuery(query);
    }

    getUpdatedQuery(item) {
        const query = new URLSearchParams(location.search);
        let qValue = query.get(item.name) ?? '';
        qValue = qValue ? qValue.split('_') : [];

        if (item.checked) {
            qValue.push(item.value);
        } else {
            qValue.splice(qValue.indexOf(item.value), 1);
        }

        qValue.sort((a, b) => a.localeCompare(b));

        if (qValue.length) {
            query.set(item.name, qValue.join('_'));
        } else {
            query.delete(item.name);
        }

        return query;
    }

    applyQuery(query) {
        const search = query.toString();
        if (search) {
            location.search = search;
        } else {
            location = location.href.split('?')[0];
        }
    }
}
