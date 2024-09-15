<?php

declare(strict_types=1);

namespace Lightna\Magento\App;

use Lightna\Engine\App\Context;
use Lightna\Engine\Data\DataA;
use Lightna\Elasticsearch\App\Client as ElasticClient;
use Lightna\Engine\Data\Request;
use Lightna\Magento\App\Entity\Product as ProductEntity;
use Lightna\Magento\Data\Category;
use Lightna\Magento\Data\Config;
use Lightna\Magento\Data\Content\Category as CategoryContent;
use Lightna\Magento\Data\Session;

class Search extends DataA
{
    protected Config $config;
    protected ElasticClient $client;
    protected Context $context;
    protected Category $category;
    protected Session $session;
    protected CategoryContent $categoryContent;
    protected Request $request;
    protected int $pageSize;

    protected function definePageSize(): void
    {
        $this->pageSize = $this->config->product->listing->defaultPageSize;
    }

    public function search(): array
    {
        $result = $this->client->search(
            'product_' . $this->context->scope,
            $this->buildQuery()
        );

        return $this->parseResult($result);
    }

    protected function buildQuery(): array
    {
        $query = $this->_buildQuery();
        $this->applyFilters($query);

        return $query;
    }

    protected function _buildQuery(): array
    {
        $categoryId = $this->category->entityId;

        return [
            'from' => 0,
            'size' => $this->pageSize,
            'stored_fields' => '_none_',
            'docvalue_fields' => ['_id', '_score'],
            'sort' => [['position_category_' . $categoryId => ['order' => 'asc']]],
            'query' => ['bool' => ['must' => [
                ['term' => ['category_ids' => $categoryId]],
                ['terms' => ['visibility' => ['2', '4']]],
            ]]],
            'aggregations' => $this->buildQueryAggregations(),
        ];
    }

    protected function applyFilters(array &$query): void
    {
        $must = &$query['query']['bool']['must'];
        foreach ($this->categoryContent->filterableAttributes as $attribute) {
            if ($this->request->param->{$attribute->code} === null) {
                continue;
            }
            $values = explode('_', $this->request->param->{$attribute->code});
            foreach ($values as $value) {
                $must[] = ['term' => [$attribute->code => $value]];
            }
        }
    }

    protected function buildQueryAggregations(): array
    {
        $facets = [
            'price_bucket' => [
                'extended_stats' => ['field' => 'price_' . $this->session->user->groupId . '_' . $this->context->scope],
            ],
            'category_bucket' => [
                'terms' => ['field' => 'category_ids', 'size' => 500],
            ],
        ];

        foreach ($this->categoryContent->filterableAttributes as $attribute) {
            if (isset($facets[$key = $attribute->code . '_bucket'])) {
                continue;
            }
            $facets[$key] = ['terms' => ['field' => $attribute->code, 'size' => 500]];
        }

        return $facets;
    }

    protected function parseResult(array $result): array
    {
        return [
            'total' => $result['hits']['total']['value'],
            'pageSize' => $this->pageSize,
            'result' => $this->parseResultItems($result),
            'facets' => $this->parseResultFacets($result),
        ];
    }

    protected function parseResultItems(array $result): array
    {
        $items = [];
        $ids = [];
        foreach ($result['hits']['hits'] as $hit) {
            $ids[] = $hit['fields']['_id'][0];
        }

        $entity = getobj(ProductEntity::class);
        foreach ($entity->getList($ids) as $data) {
            if (!empty($data)) {
                $items[] = $data;
            }
        }

        return $items;
    }

    protected function parseResultFacets(array $result): array
    {
        $facets = $this->_parseResultFacets($result);
        unset($facets['category']);

        return $facets;
    }

    protected function _parseResultFacets(array $result): array
    {
        $facets = [];
        $position = 0;
        foreach ($result['aggregations'] as $key => $agg) {
            if (empty($agg['min']) && empty($agg['buckets'])) {
                continue;
            }

            $code = preg_replace('~_bucket$~', '', $key);
            $facet = ['code' => $code];

            if (!empty($agg['min'])) {
                $facet = merge($facet, [
                    'type' => 'range',
                    'min' => $agg['min'],
                    'max' => $agg['max'],
                ]);
            } elseif (!empty($agg['buckets'])) {
                $options = [];
                foreach ($agg['buckets'] as $bucket) {
                    $options[] = [
                        'value' => $bucket['key'],
                        'count' => $bucket['doc_count'],
                    ];
                }
                $facet = merge($facet, [
                    'type' => 'option',
                    'options' => $options,
                ]);
            }

            $this->markApplied($facet, $hasAppliedOptions);
            $this->decorate($facet);
            $facet['position'] = $position;
            $facet['isInUse'] = $hasAppliedOptions;
            $facets[$code] = $facet;

            $position++;
        }

        return $facets;
    }

    protected function markApplied(array &$facet, &$hasAppliedOptions): void
    {
        $hasAppliedOptions = false;
        if (!isset($facet['options'])) {
            return;
        }

        $q = $this->request->param->{$facet['code']} ?? '';
        $q = $q !== '' ? array_flip(explode('_', $q)) : [];

        foreach ($facet['options'] as $i => $option) {
            $isApplied = isset($q[$option['value']]);
            $facet['options'][$i]['applied'] = $isApplied;
            $hasAppliedOptions = $hasAppliedOptions || $isApplied;
        }
    }

    protected function decorate(array &$facet): void
    {
        $attrs = $this->categoryContent->filterableAttributes;
        if ($attr = $attrs[camel($facet['code'])] ?? null) {
            $facet['label'] = $attr->label;
            foreach ($facet['options'] ?? [] as $i => $option) {
                $facet['options'][$i]['label'] = $attr->options[$option['value']] ?? $option['value'];
            }
        } else {
            if ($facet['code'] === 'category') {
                $facet['label'] = phrase('Category');
            } else {
                $facet['label'] = $facet['code'];
            }
            foreach ($facet['options'] ?? [] as $i => $option) {
                $facet['options'][$i]['label'] = $option['value'];
            }
        }
    }
}
