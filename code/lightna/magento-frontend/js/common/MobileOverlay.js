import { $, $$ } from 'lightna/lightna-engine/lib/utils/dom';

export class MobileOverlay {
    classes = {
        overlayOpen: 'mobile-overlay--active',
        overlayActive: 'active',
    };
    actions = {
        'toggle-overlay': (overlayId) => this.toggle(overlayId),
    };

    constructor() {
        this.initializeEventListeners();
    }

    initializeEventListeners() {
        document.body.addEventListener('click', (event) => this.handleOverlayActions(event));
    }

    handleOverlayActions(event) {
        const trigger = event.target.closest('[data-action]');
        if (!trigger) return;

        const action = trigger.getAttribute('data-action');
        const overlayId = trigger.dataset.overlayId;
        const handler = this.actions[action];

        if (handler) {
            try {
                handler(overlayId);
            } catch (error) {
                console.error(`Error handling overlay action: ${action}`, error);
            }
        }
    }

    toggle(overlayId) {
        if (!overlayId) return;

        const overlay = $(`[data-overlay="${overlayId}"]`);
        if (!overlay) return;

        const isActive = overlay.classList.toggle(this.classes.overlayActive);
        document.body.classList.toggle(this.classes.overlayOpen, isActive);
    }
}
