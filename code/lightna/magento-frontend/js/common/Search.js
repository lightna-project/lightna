import { $, $$ } from 'lightna/lightna-engine/lib/utils/dom';

export class Search {
    static minChars = 3;
    static actions = {
        search: '[data-action="search"]',
        clear: '[data-action="clear-search"]',
        open: '[data-action="open-search"]',
        close: '[data-action="close-search"]',
    };
    static selectors = {
        input: '#search',
        wrapper: '.search__wrap',
    };
    classes = {
        active: 'search__wrap--active',
        overlayActive: 'search-open',
    };

    constructor() {
        this.component = $('.cjs-search');
        if (!this.component) return;
        this.search = $(Search.selectors.input);
        this.searchWrap = $(Search.selectors.wrapper);
        this.clearAction = $(Search.actions.clear);
        this.searchAction = $(Search.actions.search);
        this.bindEvents();
        this.prefillSearchInput();
    }

    bindEvents() {
        this.search.addEventListener('input', this.onInput.bind(this));
        this.clearAction.addEventListener('click', this.onClear.bind(this));

        $$(Search.actions.open).forEach((actionTrigger) => {
            actionTrigger.addEventListener('click', this.open.bind(this));
        });

        $$(Search.actions.close).forEach((actionTrigger) => {
            actionTrigger.addEventListener('click', this.close.bind(this));
        });
    }

    prefillSearchInput() {
        const searchParams = new URLSearchParams(window.location.search);
        const searchQuery = searchParams.get('q');

        if (searchQuery) {
            this.search.value = searchQuery;
            this.search.dispatchEvent(new Event('input'));
        }
    }

    onInput(event) {
        this.toggleClearAction(!event.target.value);
        this.toggleSearchAction(event.target.value.length < Search.minChars);
    }

    onClear() {
        this.search.focus();
        this.toggleClearAction(true);
        this.toggleSearchAction(true);
    }

    toggleClearAction(force) {
        this.clearAction.classList.toggle('hidden', force);
    }

    toggleSearchAction(force) {
        this.searchAction.classList.toggle('btn-disabled', force);
    }

    close() {
        this.searchWrap.classList.remove(this.classes.active);
        document.body.classList.remove(this.classes.overlayActive);
    }

    open() {
        this.searchWrap.classList.add(this.classes.active);
        document.body.classList.add(this.classes.overlayActive);
        setTimeout(() => {
            this.search.focus();
        });
    }
}
