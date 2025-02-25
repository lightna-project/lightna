import { isElementInViewport } from 'lightna/engine/lib/utils/isElementInViewport';
import { ClickEventDelegator} from 'lightna/magento-frontend/common/ClickEventDelegator';

export class Collapsible {
    items = [];
    scrollTimeout = null;
    classes = {
        active: 'collapsible--active',
    };
    actions = {
        click: {
            'toggle-collapsible': [(event, item) => this.toggle(item)],
        },
    };
    component = '.cjs-collapsible';

    constructor() {
        this.extendProperties();
        this.initializeActions();
    }

    extendProperties() {}

    initializeActions() {
        ClickEventDelegator.add(this.actions.click);
    }

    toggle(item) {
        const container = item.closest(this.component);
        container.classList.toggle(this.classes.active);
        this.scrollIfNotVisible(item);
    }

    scrollIfNotVisible(item) {
        if (!isElementInViewport(item)) {
            clearTimeout(this.scrollTimeout);
            this.scrollTimeout = setTimeout(() => {
                item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 50);
        }
    }
}
