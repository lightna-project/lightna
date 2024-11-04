export class PageOverlayPool {
    static activeOverlays = [];

    constructor() {
        this.bindEvents();
    }

    bindEvents() {
        document.addEventListener('keydown', (event) => {
            const numActiveOverlays = PageOverlayPool.activeOverlays.length;
            if (event.key === 'Escape' && numActiveOverlays) {
                PageOverlayPool.activeOverlays[numActiveOverlays - 1].hide();
            }
        });
    }
}
