import { $, $$ } from 'lightna/engine/lib/utils/dom';
import { ClickEventDelegator } from 'lightna/magento-frontend/common/ClickEventDelegator';

export class Tabs {
    classes = {
        activeTab: 'active',
        activeTrigger: 'active',
    }
    actions = {
        click: {
            'open-tab': [(event, item) => this.openTab(item)],
        }
    };

    constructor() {
        this.component = '.cjs-tabs';
        this.initializeActions();
    }

    initializeActions() {
        ClickEventDelegator.add(this.actions.click);
    }

    openTab(item) {
        const container = item.closest(this.component);
        if (!container) return;

        this.deactivateAll(container);
        this.activateCurrent(item, container);
    }

    deactivateAll(container) {
        $$('[data-tab-trigger]', container).forEach(trigger =>
            trigger.classList.remove(this.classes.activeTrigger)
        );

        $$('[data-tab]', container).forEach(tab =>
            tab.classList.remove(this.classes.activeTab)
        );
    }

    activateCurrent(trigger, container) {
        const tabName = trigger.dataset.tabTrigger;
        if (!tabName) return;

        trigger.classList.add(this.classes.activeTrigger);
        const targetTab = $(`[data-tab="${tabName}"]`, container);
        if (targetTab) {
            targetTab.classList.add(this.classes.activeTab);
        }
    }
}
