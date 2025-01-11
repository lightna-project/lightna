<?php

declare(strict_types=1);

namespace Lightna\Magento\Data\Search;

use Lightna\Engine\Data\DataA;
use Lightna\Engine\Data\Request;

class Suggestions extends DataA
{
    protected Request $request;
    private const SEARCH_URL = '/catalogsearch/result/?q=';

    /**
     * @return Suggestion[]
     */
    public function getItems(): array
    {
        $suggestions = $this->request->param->suggestions ?? [];
        $query = $this->request->param->query ?? '';
        $result = [];

        foreach ($suggestions as $suggestion) {
            $suggestion['url'] = $this::SEARCH_URL . escape($suggestion['title'], 'url-param');
            $suggestion['query'] = $query;
            $result[] = newobj(
                Suggestion::class,
                [
                    'title' => $suggestion['title'],
                    'numResults' => (int)$suggestion['num_results'],
                    'url' => $suggestion['url'],
                    'query' => $query,
                ]
            );
        }

        return $result;
    }
}
