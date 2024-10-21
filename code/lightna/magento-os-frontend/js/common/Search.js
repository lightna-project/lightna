import { $, $$ } from 'lightna/lightna-engine/lib/utils/dom';

export class Search {
    minChars = 3;
    actions = {
        search: '[data-action="search"]',
        clear: '[data-action="clear-search"]',
        open: '[data-action="open-search"]',
        close: '[data-action="close-search"]',
    };
    classes = {
        active: 'search__wrap--active',
        overlayActive: 'search-open',
    };

    constructor() {
        this.component = $('.cjs-search');
        this.search = $('#search');
        this.searchWrap = $('.search__wrap');
        this.clearAction = $(this.actions.clear);
        this.searchAction = $(this.actions.search);
        this.bindEvents();
        this.prefillSearchInput();
    }

    bindEvents() {
        this.search.addEventListener('input', this.onInput.bind(this));
        this.clearAction.addEventListener('click', this.onClear.bind(this));

        $$(this.actions.open).forEach((actionTrigger) => {
            actionTrigger.addEventListener('click', this.open.bind(this));
        });

        $$(this.actions.close).forEach((actionTrigger) => {
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
        this.toggleSearchAction(event.target.value.length < this.minChars);
    }

    onClear(event) {
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

    close(event) {
        this.searchWrap.classList.remove(this.classes.active);
        document.body.classList.remove(this.classes.overlayActive);
    }

    open(event) {
        setTimeout(() => {
            this.search.focus();
        });
        this.searchWrap.classList.add(this.classes.active);
        document.body.classList.add(this.classes.overlayActive);
    }
}
