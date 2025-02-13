import { $, $$ } from 'lightna/engine/lib/utils/dom';
import { Blocks } from 'lightna/engine/lib/Blocks';
import { UserInput } from 'lightna/engine/lib/UserInput';

export class Options {
    cjs = '.cjs-product-options';
    blockId = 'product-options';

    constructor() {
        this.init();
    }

    init() {
        this.component = $(this.cjs);
        this.bindEvents();
    }

    bindEvents() {
        $$('[data-option]', this.component).forEach((element) => {
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
        await Blocks.updateHtml([this.blockId], UserInput.collect(this.component));

        this.init();
    }
}
