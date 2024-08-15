import { $, $$ } from '../lib/utils';

export class Tabs {
    constructor(scope) {
        this.scope = scope;
        this.triggers = $$('.cjs-tab-trigger', this.scope);
        this.tabs = $$('.cjs-tab', this.scope);
        this.bindEvents();
    }

    bindEvents() {
        this.triggers.forEach((trigger) => {
            trigger.addEventListener('click', () => {
                this.deactivateAll();
                this.activateCurrent(trigger);
            });
        });
    }

    deactivateAll() {
        this.triggers.forEach((trigger) => {
            trigger.classList.remove('active');
        });

        this.tabs.forEach((tab) => {
            tab.classList.remove('active');
        });
    }

    activateCurrent(trigger) {
        const index = trigger.dataset.tabIndex;
        trigger.classList.add('active');
        $(`[data-tab="${index}"]`, this.scope).classList.add('active');
    }
}
