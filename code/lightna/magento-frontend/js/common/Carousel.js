import { $, $$ } from 'lightna/engine/lib/utils/dom';
import { ClickEventDelegator } from 'lightna/magento-frontend/common/ClickEventDelegator';

export class Carousel {
    static selectors = {
        carouselItem: '.carousel-item',
        carouselControls: '.carousel-controls',
        carouselInner: '.carousel',
        leftArrow: '.carousel-arrow.previous',
        rightArrow: '.carousel-arrow.next',
    };
    classes = {
        initialized: 'initialized',
        controlsHidden: 'hidden',
        arrowDisabled: 'disabled',
    };
    actions = {
        click: {
            'carousel-slide-left': [(event, trigger) => this.scrollBy(trigger, -1)],
            'carousel-slide-right': [(event, trigger) => this.scrollBy(trigger, 1)],
        }
    };
    component = '.cjs-carousel';

    constructor() {
        this.extendProperties();
        this.initializeActions();
        this.observeDom();
        this.setupAll();
        this.initializeResizeListener();
    }

    extendProperties() {
    }

    initializeActions() {
        ClickEventDelegator.add(this.actions.click);
    }

    observeDom() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach(({ addedNodes }) => {
                addedNodes.forEach((node) => {
                    if (node.nodeType !== 1) return;

                    if (node.matches(this.component) || node.querySelector(this.component)) {
                        this.setupAll();
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }

    getAll() {
        return $$(this.component);
    }

    setupAll() {
        this.getAll().forEach(el => {
            if (el.classList.contains(this.classes.initialized)) return;

            el.classList.add(this.classes.initialized);
            this.updateControls(el);
            this.initializeScrollListener(el);
        });
    }

    initializeScrollListener(el) {
        const carousel = $(Carousel.selectors.carouselInner, el);
        if (!carousel) return;

        let throttleTimeout = null;
        carousel.addEventListener('scroll', () => {
            if (throttleTimeout) return;

            throttleTimeout = setTimeout(() => {
                this.updateControls(el);
                throttleTimeout = null;
            }, 100);
        });
    }

    initializeResizeListener() {
        window.addEventListener('resize', this.updateAll.bind(this));
    }

    updateAll() {
        this.getAll().forEach(el => {
            this.updateControls(el);
        });
    }

    updateControls(el) {
        const item = $(Carousel.selectors.carouselItem, el);
        const controls = $(Carousel.selectors.carouselControls, el)
        const inner = $(Carousel.selectors.carouselInner, el);
        const arrowLeft = $(Carousel.selectors.leftArrow, el)
        const arrowRight = $(Carousel.selectors.rightArrow, el)

        if (!controls) return;

        const scrollWidth = inner.scrollWidth;
        const carouselWidth = inner.offsetWidth;
        const scrollPosition = inner.scrollLeft;
        const itemWidth = item.offsetWidth;
        const tolerance = 4;

        controls.classList.toggle(this.classes.controlsHidden, scrollWidth <= carouselWidth + tolerance);
        arrowLeft.classList.toggle(this.classes.arrowDisabled, scrollPosition <= itemWidth - tolerance);
        arrowRight.classList.toggle(this.classes.arrowDisabled, scrollPosition >= scrollWidth - carouselWidth - itemWidth - tolerance);
    }

    scrollBy(trigger, direction) {
        const wrapper = trigger.closest(this.component);
        const carousel = wrapper?.querySelector(Carousel.selectors.carouselInner);
        if (!carousel) return;

        const scrollAmount = carousel.offsetWidth;
        carousel.scrollBy({
            left: direction * scrollAmount,
            behavior: 'smooth'
        });
    }
}
