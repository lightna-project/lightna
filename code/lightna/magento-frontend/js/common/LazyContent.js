import { $$ } from 'lightna/engine/lib/utils/dom';
import { Blocks } from 'lightna/engine/lib/Blocks';

export class LazyContent {
    static DATA_ATTR = 'data-lazy-block-id';
    component = '.cjs-lazy';

    constructor() {
        this.extendProperties();
        this.initializeObservers();
    }

    extendProperties() {
    }

    initializeObservers() {
        const observer = new IntersectionObserver(async (entries, observer) => {
            await this.handleIntersection(entries, observer);
        });

        this.getSections().forEach(section => observer.observe(section));
    }

    async handleIntersection(entries, observer) {
        for (const entry of entries) {
            if (entry.isIntersecting) {
                observer.unobserve(entry.target);
                await this.load(entry.target);
            }
        }
    }

    getSections() {
        return $$(this.component);
    }

    async load(section) {
        const blockId = section.getAttribute(LazyContent.DATA_ATTR);
        await Blocks.updateHtml([blockId]);
    }
}
