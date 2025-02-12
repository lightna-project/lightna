import { $, $$ } from 'lightna/lightna-engine/lib/utils/dom';
import { ClickEventDelegator } from 'lightna/magento-frontend/common/ClickEventDelegator';

export class Search {
    static minChars = 3;
    static selectors = {
        input: '#search',
        wrapper: '.search__wrap',
        searchAction: '.search__submit-btn',
        clearAction: '.search__clear-btn',
    };
    classes = {
        active: 'search__wrap--active',
        overlayActive: 'search-open',
    };
    actions = {
        'clear-search': () => { this.onClear() },
        'open-search': () => { this.open() },
        'close-search': () => { this.close() },
    };

    constructor() {
        this.component = '.cjs-search';
        if (!$(this.component)) return;

        this.search = $(Search.selectors.input);
        this.searchWrap = $(Search.selectors.wrapper);
        this.searchAction = $(Search.selectors.searchAction);
        this.clearAction = $(Search.selectors.clearAction);
        this.initializeEventListeners();
        this.initializeActions();
        this.prefillSearchInput();
    }

    initializeEventListeners() {
        this.search.addEventListener('input', (event) => { this.onInput(event) });
    }

    initializeActions() {
        ClickEventDelegator.addActions(this.actions);
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
