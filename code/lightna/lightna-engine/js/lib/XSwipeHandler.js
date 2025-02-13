import { $ } from 'lightna/engine/lib/utils/dom';
export class XSwipeHandler {
    #xDown = null;
    #yDown = null;

    constructor(options) {
        this.element = $(options.element);
        this.swipeLeft = options.onSwipeLeft ?? this.swipeLeft;
        this.swipeRight = options.onSwipeRight ?? this.swipeRight;
        this.bindEvents();
    }

    bindEvents() {
        this.element.addEventListener('touchstart', this.onTouchStart.bind(this), {
            passive: true,
        });
        this.element.addEventListener('touchmove', this.onTouchMove.bind(this), {
            passive: true,
        });
    }

    onTouchStart(event) {
        this.#xDown = event.touches[0].clientX;
        this.#yDown = event.touches[0].clientY;
    }

    swipeLeft() {
        return this;
    }

    swipeRight() {
        return this;
    }

    onTouchMove(event) {
        if (!this.#xDown || !this.#yDown) {
            return;
        }

        const xUp = event.touches[0].clientX;
        const yUp = event.touches[0].clientY;

        this.xDiff = this.#xDown - xUp;
        this.yDiff = this.#yDown - yUp;

        if (Math.abs(this.xDiff) > Math.abs(this.yDiff)) {
            if (this.xDiff > 0) {
                this.swipeLeft();
            } else {
                this.swipeRight();
            }
        }

        this.#xDown = null;
        this.#yDown = null;
    }
}
