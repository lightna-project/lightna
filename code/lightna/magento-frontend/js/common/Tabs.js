import { $, $$ } from 'lightna/lightna-engine/lib/utils/dom';

export class Tabs {
    constructor() {
        this.cjs = $$('.cjs-tabs');
        this.cjs.forEach((item) => {
            item.triggers = $$('[data-action="open-tab"]', item);
            item.tabs = $$('.tab-content', item);
        });
        this.bindEvents();
    }

    bindEvents() {
        this.cjs.forEach((item) => {
            item.triggers.forEach((trigger) => {
                trigger.addEventListener('click', () => {
                    this.deactivateAll(item);
                    this.activateCurrent(item, trigger);
                });
            });
        });
    }

    deactivateAll(item) {
        item.triggers.forEach((trigger) => {
            trigger.classList.remove('active');
        });

        item.tabs.forEach((tab) => {
            tab.classList.remove('active');
        });
    }

    activateCurrent(item, trigger) {
        const index = trigger.dataset.tabIndex;
        trigger.classList.add('active');
        $(`[data-tab="${index}"]`, item).classList.add('active');
    }
}
