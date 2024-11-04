import { $ } from 'lightna/lightna-engine/lib/utils/dom';
import { PageOverlayPool } from 'lightna/magento-os-frontend/common/PageOverlayPool';

export class PageOverlay {
    classes = {
        overlay: 'page-overlay',
        overlayActive: 'page-overlay--active',
        overlayContentActive: 'overlay__content--active',
    };
    overlay = null;
    overlayContent = null;

    constructor(overlayParent, overlayFor) {
        this.create(overlayParent);
        this.overlayContent = $(`[data-overlay-content=${overlayFor}]`);
        if (!this.overlayContent) {
            throw new Error(`Overlay content for ${overlayFor} not found`);
        }
        this.bindEvents();
    }

    create(overlayParent) {
        this.overlay = document.createElement('div');
        this.overlay.classList.add(this.classes.overlay);
        $(overlayParent).appendChild(this.overlay);
    }

    bindEvents() {
        this.overlay.addEventListener('click', (event) => {
            if (event.target === this.overlay) {
                this.hide();
            }
        });
    }

    show() {
        this.overlayContent.classList.add(this.classes.overlayContentActive);
        this.overlay.classList.add(this.classes.overlayActive);
        PageOverlayPool.activeOverlays.push(this);
    }

    hide() {
        this.overlayContent.classList.remove(this.classes.overlayContentActive);
        this.overlay.classList.remove(this.classes.overlayActive);
        PageOverlayPool.activeOverlays.pop();
    }
}
