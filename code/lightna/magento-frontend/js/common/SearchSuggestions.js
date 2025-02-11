import { $ } from 'lightna/lightna-engine/lib/utils/dom';
import { Request } from 'lightna/lightna-engine/lib/Request';
import { Blocks } from 'lightna/lightna-engine/lib/Blocks';
import { Search } from 'lightna/magento-frontend/common/Search';
import { ClickEventDelegator } from 'lightna/magento-frontend/common/ClickEventDelegator';

export class SearchSuggestions {
    searchSuggestUrl = '/search/ajax/suggest';
    suggestions = [];
    maxSuggestions = 7;
    blockId = 'search-suggestions';
    component = '.cjs-search-suggestions';
    debounceTimer = null;
    debounceTimeout = 300;
    abortController = null;
    actions = {
        click: {
            'clear-search': [() => this.clearSuggestions()],
        }
    }

    constructor() {
        this.search = $(Search.selectors.input);
        if (!this.search) return;
        this.initializeEventListeners();
        this.initializeActions();
    }

    initializeEventListeners() {
        this.search.addEventListener('input', async () => { await this.updateSuggestions(); });
    }

    initializeActions() {
        ClickEventDelegator.add(this.actions.click);
    }

    async updateSuggestions() {
        clearTimeout(this.debounceTimer);

        if (this.search.value.length < Search.minChars) {
            if (this.suggestions.length) {
                this.clearSuggestions();
            }

            return;
        }

        this.debounceTimer = setTimeout(async() => {
            await this.getSuggestions();
            await this.updateSuggestionsHtml();
        }, this.debounceTimeout);
    }

    async getSuggestions() {
        this.abortController?.abort();
        this.abortController = new AbortController();
        this.suggestions = await Request.get(
            `${this.searchSuggestUrl}?q=${encodeURIComponent(this.search.value)}`,
            { signal: this.abortController.signal }
        ) || [];
    }

    async updateSuggestionsHtml() {
        return Blocks.updateHtml([this.blockId], {
            suggestions: this.limitSuggestions(),
            query: this.search.value,
        });
    }

    limitSuggestions() {
        return this.suggestions.slice(0, this.maxSuggestions);
    }

    clearSuggestions() {
        clearTimeout(this.debounceTimer);
        this.abortController?.abort();
        this.suggestions = [];
        $(this.component).innerHTML = '';
    }
}
