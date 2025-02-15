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
    classes = {
        activeItem: 'active',
        activeOverlay: 'overlay-active',
        hoverItem: 'hover',
        preventClick: 'prevent-click',
        noJs: 'no-js',
    };
    selectors = {
        itemTopLevelTitle: '.level-1__title',
        itemWithChildren: '.level-1.has-children .level-1__title',
        itemTopLevel: '.level-1',
        hasChildren: '.has-children',
        menuContainer: '.main-navigation',
    };

    constructor(options = {}) {
        this.menuContainer = $(this.selectors.menuContainer);
        if (!this.menuContainer) return;

        this.delay = isTouchDevice() ? 0 : options.delay || 150;
        this.itemsWithChildren = $$(this.selectors.itemWithChildren);
        this.itemsTopLevel = $$(this.selectors.itemTopLevel);
        
        this.menuContainer.classList.remove(this.classes.noJs);
        this.initializeEventListeners();
    }

    initializeEventListeners() {
        document.addEventListener('mouseenter', this.preventMenuActivation.bind(this), { once: true });
        this.itemsWithChildren.forEach(item => {
            item.addEventListener('click', this.onItemWithChildrenClick.bind(this, item));
        });

        this.itemsTopLevel.forEach(item => {
            item.addEventListener('mouseenter', this.onItemTopLevelMouseEnter.bind(this, item), { passive: true });
            item.addEventListener('mouseleave', this.onItemTopLevelMouseLeave.bind(this, item), { passive: true });
        });
    }

    preventMenuActivation() {
        if (isTouchDevice()) return;
        this.mouseEnterBlocked = this.menuContainer.matches(':hover');
    }

    onItemWithChildrenClick(item, event) {
        if (window.innerWidth < this.mobileBreakpoint) {
            this.toggleMobileSubmenu(item);
            event.preventDefault();
            return;
        }

        if (item.matches(`.${this.classes.preventClick}`)) {
            event.preventDefault();
            item.classList.remove(this.classes.preventClick);
        }
    }

    onItemTopLevelMouseEnter(item) {
        if (this.mouseEnterBlocked) return;

        if (isTouchDevice()) {
            $(this.selectors.itemTopLevelTitle, item).classList.add(this.classes.preventClick);
        }

        this.mouseEnterTimeout = setTimeout(() => {
            const activeItem = $(`.${this.classes.hoverItem}`, this.menuContainer);
            activeItem?.classList.remove(this.classes.hoverItem);
            item.classList.add(this.classes.hoverItem);
            this.toggleOverlay(true, item.matches(this.selectors.hasChildren));
        }, this.delay);
    }

    onItemTopLevelMouseLeave(item) {
        this.mouseEnterBlocked = false;
        clearTimeout(this.mouseEnterTimeout);

        const isPrevHover = item.previousElementSibling?.matches(':hover');
        const isNextHover = item.nextElementSibling?.matches(':hover');
        if (isPrevHover || isNextHover) {
            return;
        }

        setTimeout(() => {
            if (item.matches(':hover')) return;
            const currentHover = $(`.${this.classes.hoverItem}`, this.menuContainer);
            if (currentHover) {
                currentHover.classList.remove(this.classes.hoverItem);
                this.toggleOverlay(false);
            }
        }, this.delay);
    }

    toggleMobileSubmenu(item) {
        item.parentNode.classList.toggle(this.classes.activeItem);
    }

    toggleOverlay(show, hasChildren = false) {
        const shouldShow = show && hasChildren;
        if (document.body.classList.contains(this.classes.activeOverlay) !== shouldShow) {
            document.body.classList.toggle(this.classes.activeOverlay, shouldShow);
        }
    }
}
