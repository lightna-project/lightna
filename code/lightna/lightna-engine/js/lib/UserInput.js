import { $ } from 'lightna/engine/lib/utils/dom';

export class UserInput {
    static collect(container, skipIfNotDisplayed) {
        // skipIfNotDisplayed = true by default
        skipIfNotDisplayed = typeof skipIfNotDisplayed === 'undefined' ? true : skipIfNotDisplayed;

        let data = {};
        if (!$(container)) {
            return data;
        }
        let inputs = this.getInputElements(container);

        for (let i = 0; i < inputs.length; i++) {
            let input = inputs[i];
            if (skipIfNotDisplayed && input.type !== 'hidden' && input.offsetHeight === 0 && input.offsetWidth === 0) {
                continue;
            }
            let name = input.getAttribute('name');
            if (name) {
                if (input.type === 'radio' && !input.checked) {
                    continue;
                }
                if (input.type === 'checkbox') {
                    data[name] = input.checked;
                    continue;
                }
                data[name] = input.value;
            }
        }

        // expand names to arrays for names like "array[field]"
        let dataFinal = {};
        for (let i in data) {
            if (!data.hasOwnProperty(i)) continue;
            let path = i.replace(/]/g, '').split('[');
            let dest = dataFinal;
            for (let j = 0; j < path.length - 1; j++) {
                if (typeof dest[path[j]] !== 'object') dest[path[j]] = {};
                dest = dest[path[j]];
            }
            dest[path[path.length - 1]] = data[i];
        }

        return dataFinal;
    }

    static getInputElements(container) {
        container = $(container);
        if (!container) {
            return [];
        }

        let elements = [];
        let toFind = ['input', 'select', 'button', 'textarea'];
        for (let i = 0; i < toFind.length; i++) {
            elements = elements.concat(Array.prototype.slice.call(container.getElementsByTagName(toFind[i])));
        }

        return elements;
    }
}
