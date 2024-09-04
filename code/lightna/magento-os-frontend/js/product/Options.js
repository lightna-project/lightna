import { $, $$, getBlockHtml } from '../lib/utils';
import { UserInput } from '../lib/UserInput';

export class ProductOptions {
    cjs = '.cjs-product-options';
    block = '.body.main.content.main.cta.add-to-cart.options';

    constructor() {
        this.init();
    }

    init() {
        this.component = $(this.cjs);
        this.bindEvents();
    }

    bindEvents() {
        $$('[data-option]', this.component).foreach((i, element) => {
            if (element.classList.contains('disabled')) {
                return;
            }
            const option = JSON.parse(element.dataset.option);

            element.addEventListener('click', () => {
                this.optionClick(element, option);
            });
        });
    }

    async optionClick(element, option) {
        $('#option_' + option.attributeCode).value = option.id;
        this.component.outerHTML = await getBlockHtml(this.block, UserInput.collect(this.component));

        this.init();
    }
}
