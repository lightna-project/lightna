import { $$, isElementInViewport } from '../lib/utils';
export class Collapsible {
    items = [];

    constructor(scope, options = {}) {
        this.scope = scope;
        this.options = options;
        this.init();
        this.bindEvents();
    }

    init() {
        const jsc = $$('.cjs-collapsible', this.scope);
        jsc.forEach((item) => {
            const trigger = item.querySelector('.cjs-collapsible-trigger');
            if (trigger) {
                this.items.push({
                    container: item,
                    trigger,
                });
            }
        });
    }

    bindEvents() {
        this.items.forEach((item) => {
            item.trigger.addEventListener('click', () => {
                this.onToggle(item);
            });
        });
    }

    onToggle(item) {
        if (this.options.type === 'accordion') {
            this.items.forEach((i) => {
                if (i !== item) {
                    i.container.classList.remove('collapsible--active');
                }
            });
        }
        item.container.classList.toggle('collapsible--active');
        this.scrollIfNotVisible(item.trigger);
    }

    scrollIfNotVisible(trigger) {
        if (!isElementInViewport(trigger)) {
            trigger.scrollIntoView();
        }
    }
}
