import { $ } from 'lightna/lightna-engine/lib/utils/dom';
import { Request } from 'lightna/lightna-engine/lib/Request';
import { Blocks } from 'lightna/lightna-engine/lib/Blocks';
import { Search } from 'lightna/magento-os-frontend/common/Search';

export class SearchSuggestions {
    searchSuggestUrl = '/search/ajax/suggest';
    suggestions = [];
    maxSuggestions = 7;
    blockId = 'search-suggestions';
    component = '.cjs-search-suggestions';
    debounceTimer = null;
    debounceTimeout = 300;
    abortController = null;

    constructor() {
        this.search = $(Search.selectors.input);
        this.clearAction = $(Search.actions.clear);
        this.bindEvents();
    }

    bindEvents() {
        this.search.addEventListener('input', this.updateSuggestions.bind(this));
        this.clearAction.addEventListener('click', this.clearSuggestions.bind(this));
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
        );
    }

    async updateSuggestionsHtml() {
        return Blocks.updateHtml([this.blockId], {
            suggestions: this.limitSuggestions(this.suggestions),
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
