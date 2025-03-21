import { $, $$ } from 'lightna/engine/lib/utils/dom';
import { Blocks } from 'lightna/engine/lib/Blocks';
import { UserInput } from 'lightna/engine/lib/UserInput';

export class Options {
    static OPTIONS_BLOCK_ID = 'product-options';
    classes = {
        active: 'active',
        disabled: 'disabled',
    };
    component = '.cjs-product-options';

    constructor() {
        this.extendProperties();
        this.initializeEventListeners();
    }

    extendProperties() {
    }

    initializeEventListeners() {
        $$(this.component).forEach((component) => {
            component.addEventListener('click', (event) => this.onOptionClick(event, component));
        });
    }

    async onOptionClick(event, component) {
        const option = event.target.closest('[data-option]');
        if (!option ||
            option.classList.contains(this.classes.active) ||
            option.classList.contains(this.classes.disabled)) {
            return;
        }

        let optionData;
        try {
            optionData = JSON.parse(option.dataset.option);
        } catch (error) {
            console.error('Invalid JSON in data-option attribute', error);
            return;
        }

        const optionInput = $(`[data-option-code="${CSS.escape(optionData.attributeCode)}"]`, component);
        if (optionInput) {
            optionInput.value = optionData.id;
        }

        await Blocks.updateHtml([Options.OPTIONS_BLOCK_ID], UserInput.collect(component));
        this.initializeEventListeners();
    }
}
