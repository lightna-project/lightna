import { $, $$ } from 'lightna/engine/lib/utils/dom';
import { isTouchDevice } from 'lightna/engine/lib/utils/isTouchDevice';

export class Menu {
    menuContainer;
    itemsTopLevel;
    itemsWithChildren;
    delay;
    mobileBreakpoint = 768;
    mouseEnterTimeout = null;
    mouseEnterBlocked = false;
    classNames = {
        activeItem: 'active',
        activeOverlay: 'overlay-active',
        hoverItem: 'hover',
        preventClick: 'prevent-click',
    };
    selectors = {
        itemTopLevelTitle: '.level-1__title',
        itemsWithChildren: '.level-1.has-children .level-1__title',
        itemsTopLevel: '.level-1',
        hasChildren: '.has-children',
        menuContainer: '.main-navigation',
    };

    constructor(options = {}) {
        this.delay = isTouchDevice() ? 0 : options.delay || 150;
        this.menuContainer = $(this.selectors.menuContainer);
        this.itemsWithChildren = $$(this.selectors.itemsWithChildren);
        this.itemsTopLevel = $$(this.selectors.itemsTopLevel);
        if (!this.menuContainer) return;
        this.menuContainer.classList.remove('no-js');
        this.bindEvents();
    }

    bindEvents() {
        document.addEventListener(
          'mouseenter',
          this.preventMenuActivation.bind(this),
          { once: true },
        );

        this.itemsWithChildren.forEach((item) => {
            item.addEventListener(
              'click',
              this.itemWithChildrenClick.bind(this, item),
            );
        });

        this.itemsTopLevel.forEach((item) => {
            item.addEventListener(
              'mouseenter',
              this.itemTopLevelMouseEnter.bind(this, item),
            );
            item.addEventListener(
              'mouseleave',
              this.itemTopLevelMouseLeave.bind(this, item),
            );
        });
    }

    preventMenuActivation() {
        if (isTouchDevice()) return;

        this.itemsTopLevel.forEach((item) => {
            if (item.matches(':hover')) {
                this.mouseEnterBlocked = true;
            }
        });
    }

    itemWithChildrenClick(item, event) {
        if (window.innerWidth < this.mobileBreakpoint) {
            this.toggleMobileSubmenu(item);
            event.preventDefault();
            return;
        }

        if (item.matches(`.${this.classNames.preventClick}`)) {
            event.preventDefault();
            item.classList.remove(this.classNames.preventClick);
        }
    }

    itemTopLevelMouseEnter(item) {
        if (this.mouseEnterBlocked) return;

        if (isTouchDevice()) {
            $(this.selectors.itemTopLevelTitle, item).classList.add(
              this.classNames.preventClick,
            );
        }

        this.mouseEnterTimeout = setTimeout(() => {
            const activeItem = $(
              `.${this.classNames.hoverItem}`,
              this.menuContainer,
            );
            activeItem?.classList.remove(this.classNames.hoverItem);
            item.classList.add(this.classNames.hoverItem);
            this.toggleOverlay(true, item.matches(this.selectors.hasChildren));
        }, this.delay);
    }

    itemTopLevelMouseLeave(item) {
        this.mouseEnterBlocked = false;
        clearTimeout(this.mouseEnterTimeout);
        const isPrevHover = item.previousElementSibling?.matches(':hover');
        const isNextHover = item.nextElementSibling?.matches(':hover');

        if (isPrevHover || isNextHover) {
            return;
        }

        setTimeout(() => {
            const currentHover = $(
              `.${this.classNames.hoverItem}`,
              this.menuContainer,
            );
            if (!currentHover) return;

            currentHover.classList.remove(this.classNames.hoverItem);
            this.toggleOverlay(false);
        }, this.delay);
    }

    toggleMobileSubmenu(item) {
        item.parentNode.classList.toggle(this.classNames.activeItem);
    }

    toggleOverlay(show, hasChildren = false) {
        document.body.classList.toggle(
          this.classNames.activeOverlay,
          show && hasChildren,
        );
    }
}
