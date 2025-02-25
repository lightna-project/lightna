import { $ } from 'lightna/engine/lib/utils/dom';
import { ClickEventDelegator} from 'lightna/magento-frontend/common/ClickEventDelegator';

export class MobileOverlay {
    classes = {
        overlayOpen: 'mobile-overlay--active',
        overlayActive: 'active',
    };
    actions = {
        click: {
            'toggle-overlay': [(event, trigger) => this.toggle(trigger)],
        }
    };

    constructor() {
        this.extendProperties();
        this.initializeActions();
    }

    extendProperties() {}

    initializeActions() {
        ClickEventDelegator.add(this.actions.click);
    }

    toggle(trigger) {
        const overlayId = trigger.dataset.overlayId;
        if (!overlayId) return;

        const overlay = $(`[data-overlay="${overlayId}"]`);
        if (!overlay) return;

        const isActive = overlay.classList.toggle(this.classes.overlayActive);
        document.body.classList.toggle(this.classes.overlayOpen, isActive);
    }
}
