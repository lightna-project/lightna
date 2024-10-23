import { $, $$ } from 'lightna/lightna-engine/lib/utils/dom';
import { Request } from 'lightna/lightna-engine/lib/Request';
import { Blocks } from 'lightna/lightna-engine/lib/Blocks';
import { Search } from 'lightna/magento-os-frontend/common/Search';

export class SearchSuggestions extends Search {
    searchSuggestUrl = '/search/ajax/suggest';
    suggestions = [];
    maxSuggestions = 7;
    blockId = 'search-suggestions';
    component = '.cjs-search-suggestions';

    bindEvents() {
        this.search.addEventListener('input', this.updateSuggestions.bind(this));
        this.clearAction.addEventListener('click', this.clearSuggestions.bind(this));
    }

    async updateSuggestions() {
        if (this.search.value.length < this.minChars) {
            this.clearSuggestions();
            return;
        }

        await this.getSuggestions();
        await this.updateSuggestionsHtml();
    }

    async getSuggestions() {
        this.suggestions = await Request.get(
            `${this.searchSuggestUrl}?q=${encodeURIComponent(this.search.value)}`,
        );
        this.limitSuggestions();
    }

    async updateSuggestionsHtml() {
        return Blocks.updateHtml([this.blockId], {
            suggestions: this.suggestions,
        });
    }

    limitSuggestions() {
        this.suggestions = this.suggestions.slice(0, this.maxSuggestions);
    }

    clearSuggestions() {
        $(this.component).innerHTML = '';
    }
}
