import { $, $$ } from 'lightna/lightna-engine/lib/utils/dom';

export class MobileOverlay {
    mobileOverlayTriggers;

    constructor() {
        this.mobileOverlayTriggers = $$('.cjs-toggle-overlay');
        this.bindEvents();
    }

    bindEvents() {
        this.mobileOverlayTriggers.forEach((trigger) => {
            trigger.addEventListener('click', () => {
                this.toggleMobileOverlay(trigger.dataset.overlay);
            });
        });
    }

    toggleMobileOverlay(overlayId) {
        const overlay = $(`[data-overlay-id="${overlayId}"]`);
        overlay.classList.toggle('active');
        document.body.classList.toggle('mobile-overlay--active');
    }
}
