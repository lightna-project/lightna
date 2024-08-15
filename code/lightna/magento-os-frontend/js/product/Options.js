import { $, $$ } from '../lib/utils';

export class ProductOptions {
    constructor() {
        this.jsc = $('.cjs-product-options');
        this.bindEvents();
    }

    bindEvents() {
        $$('[data-option]', this.jsc).foreach((i, element) => {
            const option = JSON.parse(element.dataset.option);

            element.addEventListener('click', () => {
                this.optionClick(element, option);
            });
        });
    }

    optionClick(element, option) {
        $$('.options-' + option.attributeCode + ' [data-option]').foreach((i, el) => {
            el.classList.remove('active');
        });

        element.classList.add('active');
        $('#option_' + option.attributeCode).value = option.id;
    }
}
