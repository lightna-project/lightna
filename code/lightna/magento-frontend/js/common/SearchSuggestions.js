import { $ } from 'lightna/engine/lib/utils/dom';
import { Request } from 'lightna/engine/lib/Request';
import { Blocks } from 'lightna/engine/lib/Blocks';
import { Search } from 'lightna/magento-frontend/common/Search';
import { ClickEventDelegator } from 'lightna/magento-frontend/common/ClickEventDelegator';

export class SearchSuggestions {
    static SEARCH_SUGGEST_URL= '/search/ajax/suggest';
    static SUGGESTIONS_BLOCK_ID= 'search-suggestions';
    static MAX_SUGGESTIONS= 7;
    suggestions = [];
    debounceTimer = null;
    debounceTimeout = 300;
    abortController = null;
    actions = {
        click: {
            'clear-search': [() => this.clearSuggestions()],
        }
    }
    component = '.cjs-search-suggestions';

    constructor() {
        this.extendProperties();
        this.search = $(Search.selectors.input);
        if (!this.search) return;
        this.initializeEventListeners();
        this.initializeActions();
    }

    extendProperties() {
    }

    initializeEventListeners() {
        this.search.addEventListener('input', async () => {
            await this.updateSuggestions();
        });
    }

    initializeActions() {
        ClickEventDelegator.add(this.actions.click);
    }

    async updateSuggestions() {
        clearTimeout(this.debounceTimer);

        if (this.search.value.length < Search.MIN_CHARS) {
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
            `${SearchSuggestions.SEARCH_SUGGEST_URL}?q=${encodeURIComponent(this.search.value)}`,
            { signal: this.abortController.signal }
        ) || [];
    }

    async updateSuggestionsHtml() {
        return Blocks.updateHtml([SearchSuggestions.SUGGESTIONS_BLOCK_ID], {
            suggestions: this.limitSuggestions(),
            query: this.search.value,
        });
    }

    limitSuggestions() {
        return this.suggestions.slice(0, SearchSuggestions.MAX_SUGGESTIONS);
    }

    clearSuggestions() {
        clearTimeout(this.debounceTimer);
        this.abortController?.abort();
        this.suggestions = [];
        $(this.component).innerHTML = '';
    }
}
