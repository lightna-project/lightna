import { $, $$ } from 'lightna/lightna-engine/lib/utils/dom';
import { XSwipeHandler } from 'lightna/lightna-engine/lib/XSwipeHandler';

export class Gallery {
    constructor() {
        this.offset = 1;
        this.cjs = '.cjs-gallery';
        if (!$(this.cjs)) {
            return;
        }

        this.init();
        if (this.size > 1) {
            this.activateArrows();
            this.activateThumbnailsArrows();
        }
        this.bindEvents();
        this.updateTransitionStep();
        this.handleTouch();
        this.changeImage(this.currentImage);
    }

    init() {
        this.currentImage = 0;
        this.step = 0;
        this.elements = {
            thumbnailsContainer: $('.gallery-thumbnails'),
            thumbnails: $$('.gallery-thumbnail'),
            dots: $$('.gallery-dot'),
            progress: $('.gallery-progress'),
            slider: $('.gallery-slider'),
            arrowPrev: $('.gallery-arrow.previous'),
            arrowNext: $('.gallery-arrow.next'),
            thumbsArrowPrev: $('.gallery-thumbnails-arrow.previous'),
            thumbsArrowNext: $('.gallery-thumbnails-arrow.next'),
        };
        this.size = this.elements.thumbnails.length;
    }

    bindEvents() {
        this.elements.arrowNext.addEventListener('click', this.onArrowNextClick.bind(this));
        this.elements.arrowPrev.addEventListener('click', this.onArrowPrevClick.bind(this));
        this.elements.thumbsArrowNext.addEventListener('click', this.onThumbArrowNextClick.bind(this));
        this.elements.thumbsArrowPrev.addEventListener('click', this.onThumbArrowPrevClick.bind(this));
        this.elements.thumbnailsContainer.addEventListener('scroll', this.onThumbnailsScroll.bind(this));
        this.elements.thumbnails.forEach((thumbnail) => {
            thumbnail.addEventListener('click', this.onThumbnailClick.bind(this));
        });
        this.elements.dots.forEach((dot) => {
            dot.addEventListener('click', this.onDotClick.bind(this));
        });
        window.addEventListener('resize', this.onResize.bind(this));
    }

    handleTouch() {
        new XSwipeHandler({
            element: $('.gallery-preview'),
            onSwipeLeft: () => {
                this.onSwipeLeft();
            },
            onSwipeRight: () => {
                this.onSwipeRight();
            },
        });
    }

    onArrowNextClick() {
        this.currentImage++;
        this.changeImage(this.currentImage);
    }

    onArrowPrevClick() {
        this.currentImage--;
        this.changeImage(this.currentImage);
    }

    onThumbArrowNextClick() {
        const thumbnailsHeight = this.elements.thumbnailsContainer.getBoundingClientRect().height;
        this.elements.thumbnailsContainer.scrollTop += thumbnailsHeight;
    }

    onThumbArrowPrevClick() {
        const thumbnailsHeight = this.elements.thumbnailsContainer.getBoundingClientRect().height;
        this.elements.thumbnailsContainer.scrollTop -= thumbnailsHeight;
    }

    onThumbnailClick(event) {
        this.onNavigationClick('thumbnails', event.currentTarget);
    }

    onDotClick(event) {
        this.onNavigationClick('dots', event.currentTarget);
    }

    onNavigationClick(navType, target) {
        const index = this.elements[navType].indexOf(target);
        if (index === this.currentImage) return;
        this.currentImage = index;
        this.changeImage(index);
    }

    onResize() {
        if (this.resizeTimeout) {
            window.cancelAnimationFrame(this.resizeTimeout);
        }

        this.resizeTimeout = window.requestAnimationFrame(() => {
            this.updateTransitionStep();
            this.changeImage(this.currentImage);
        });
    }

    onThumbnailsScroll() {
        const accuracy = 2;
        const notScrollableToTheBottom =
            this.elements.thumbnailsContainer.scrollTop + accuracy + this.elements.thumbnailsContainer.offsetHeight >
            this.elements.thumbnailsContainer.scrollHeight;
        const notScrollableToTheTop = this.elements.thumbnailsContainer.scrollTop < accuracy;

        this.elements.thumbsArrowNext.classList.toggle('disabled', notScrollableToTheBottom);
        this.elements.thumbsArrowPrev.classList.toggle('disabled', notScrollableToTheTop);
    }

    onSwipeLeft() {
        if (this.currentImage < this.size - 1) {
            this.currentImage++;
            this.changeImage(this.currentImage);
        }
    }

    onSwipeRight() {
        if (this.currentImage > 0) {
            this.currentImage--;
            this.changeImage(this.currentImage);
        }
    }

    updateTransitionStep() {
        const slideWidth = $('.gallery-slide').getBoundingClientRect().width;
        this.step = slideWidth + this.offset;
    }

    updateProgress() {
        if (!this.elements.progress) return;
        const progress = (100 / this.size) * (this.currentImage + 1);
        this.elements.progress.style = `width: ${progress}%`;
    }

    updateArrows() {
        this.elements.arrowPrev.classList.remove('disabled');
        this.elements.arrowNext.classList.remove('disabled');
        if (this.currentImage === 0) {
            this.elements.arrowPrev.classList.add('disabled');
        }
        if (this.currentImage === this.size - 1) {
            this.elements.arrowNext.classList.add('disabled');
        }
    }

    updateNavigation(navType) {
        this.elements[navType].forEach((item, index) => {
            item.classList.remove('active');
            if (index === this.currentImage) {
                item.classList.add('active');
            }
        });
    }

    updateThumbnailsScrollPosition() {
        const activeThumbnail = this.elements.thumbnails[this.currentImage];
        activeThumbnail.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest',
        });
    }

    activateArrows() {
        this.elements.arrowNext.classList.remove('hidden');
        this.elements.arrowPrev.classList.remove('hidden');
    }

    activateThumbnailsArrows() {
        if (this.elements.thumbnailsContainer.scrollHeight > this.elements.thumbnailsContainer.clientHeight) {
            this.elements.thumbsArrowNext.classList.remove('disabled');
        }
    }

    changeImage(index) {
        this.elements.slider.style = `transform: translate3d(-${this.step * index}px, 0, 0)`;
        this.updateArrows();
        this.updateNavigation('thumbnails');
        this.updateNavigation('dots');
        this.updateProgress();
        this.updateThumbnailsScrollPosition();
    }
}
