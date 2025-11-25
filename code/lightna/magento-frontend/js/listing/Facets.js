import { $, $$ } from 'lightna/engine/lib/utils/dom';

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
                this.onOptionChange(element);
            }
        });

        $$(this.component + ' .toggle-link').forEach((toggle) => {
            toggle.addEventListener('click', (event) => {
                event.preventDefault();
                toggle.classList.toggle('active');
            });
        });

        $$(this.component + ' [data-facet-type=range]').forEach((range) => {
            const applyButton = $('button', range);
            const rangeInputs = $$('input', range);
            const minInput = rangeInputs[0];
            const maxInput = rangeInputs[1];

            rangeInputs.forEach((input) => {
                input.addEventListener('keypress', (event) => {
                    if (event.key === 'Enter') {
                        this.onRangeChange(range);
                    }
                });

                input.addEventListener('input', (event) => {
                    const toDisable =
                        minInput.value === minInput.dataset['current'] &&
                        maxInput.value === maxInput.dataset['current'];
                    applyButton.toggleAttribute('disabled', toDisable);
                });
            });

            applyButton.addEventListener('click', () => {
                this.onRangeChange(range);
            });
        });
    }

    onOptionChange(item) {
        const query = this.getOptionUpdatedQuery(item);
        this.applyQuery(query);
    }

    onRangeChange(item) {
        const query = this.getRangeUpdatedQuery(item);
        this.applyQuery(query);
    }

    getOptionUpdatedQuery(item) {
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

    getRangeUpdatedQuery(item) {
        let code = item.dataset.facetCode,
            inputs = $$('input', item),
            value = inputs[0].value + '-' + inputs[1].value;

        const query = new URLSearchParams(location.search);
        if (value === '-') {
            query.delete(code);
        } else {
            query.set(code, value);
        }

        return query;
    }

    applyQuery(query) {
        query.delete('p');
        const search = query.toString();
        if (search) {
            location.search = search;
        } else {
            location = location.href.split('?')[0];
        }
    }
}
