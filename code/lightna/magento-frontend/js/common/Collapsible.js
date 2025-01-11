import { $$ } from 'lightna/lightna-engine/lib/utils/dom';
import { isElementInViewport } from 'lightna/lightna-engine/lib/utils/isElementInViewport';

export class Collapsible {
    items = [];

    constructor() {
        this.init();
        this.bindEvents();
    }

    init() {
        const cjs = $$('.cjs-collapsible');
        cjs.forEach((item) => {
            const trigger = item.querySelector('[data-action="toggle-collapsible"]');
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
        item.container.classList.toggle('collapsible--active');
        this.scrollIfNotVisible(item.trigger);
    }

    scrollIfNotVisible(trigger) {
        if (!isElementInViewport(trigger)) {
            trigger.scrollIntoView();
        }
    }
}
