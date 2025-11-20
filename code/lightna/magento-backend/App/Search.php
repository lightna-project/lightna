<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App;

use Lightna\Elasticsearch\App\Client as ElasticClient;
use Lightna\Engine\App\Context;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\Data\Request;
use Lightna\Magento\Backend\App\Entity\Product as ProductEntity;
use Lightna\Magento\Backend\App\Search\ClientInterface as SearchClientInterface;
use Lightna\Magento\Backend\Data\Category;
use Lightna\Magento\Backend\Data\Config;
use Lightna\Magento\Backend\Data\Content\Category as CategoryContent;
use Lightna\Magento\Backend\Data\Content\Product\FilterableAttribute;
use Lightna\Magento\Backend\Data\Session;

class Search extends ObjectA
{
    protected Config $config;
    protected SearchClientInterface $client;
    protected Context $context;
    protected Category $category;
    protected Session $session;
    protected CategoryContent $categoryContent;
    protected Request $request;
    protected int $pageSize;
    protected int $currentPage;

    public function search(): array
    {
        $result = $this->client->search(
            $this->getIndexName('product'),
            $this->buildQuery()
        );

        return $this->parseResult($result);
    }

    /** @noinspection PhpUnused */
    protected function defineClient(): void
    {
        $this->client = getobj(ElasticClient::class);
    }

    /** @noinspection PhpUnused */
    protected function defineCurrentPage(): void
    {
        $this->currentPage = max(abs((int)($this->request->param->p ?? 1)), 1);
    }

    /** @noinspection PhpUnused */
    protected function definePageSize(): void
    {
        $this->pageSize = $this->config->product->listing->defaultPageSize;
    }

    protected function getIndexName(string $entityName): string
    {
        return $entityName . '_' . $this->context->scope;
    }

    protected function buildQuery(): array
    {
        return [
            'from' => ($this->currentPage - 1) * $this->pageSize,
            'size' => $this->pageSize,
            'stored_fields' => '_none_',
            'docvalue_fields' => ['_id', '_score'],
            'sort' => $this->buildQuerySorting(),
            'query' => $this->buildQueryFilters(),
            'aggregations' => $this->buildQueryAggregations(),
        ];
    }

    protected function buildQuerySorting(): array
    {
        $order = $this->request->param->product_list_dir === 'desc' ? 'desc' : 'asc';

        return match ($this->request->param->product_list_order) {
            'price' => [[$this->getQueryField('price') => ['order' => $order]]],
            default => [['position_category_' . $this->category->entityId => ['order' => 'asc']]],
        };
    }

    protected function buildQueryFilters(): array
    {
        $filters = ['bool' => ['must' => [
            ['term' => ['category_ids' => $this->category->entityId]],
            ['terms' => ['visibility' => ['2', '4']]],
        ]]];

        foreach ($this->categoryContent->filterableAttributes as $attribute) {
            if ($this->request->param->{$attribute->code} === null) {
                continue;
            }

            foreach ($this->buildQueryAttributeFilters($attribute) as $must) {
                $filters['bool']['must'][] = $must;
            }
        }

        return $filters;
    }

    protected function buildQueryAttributeFilters(FilterableAttribute $attribute): array
    {
        if ($this->isRangeAttribute($attribute)) {
            return $this->buildQueryRangeAttributeFilters($attribute);
        } else {
            return $this->buildQueryOptionAttributeFilters($attribute);
        }
    }

    protected function buildQueryOptionAttributeFilters(FilterableAttribute $attribute): array
    {
        $values = explode('_', $this->request->param->{$attribute->code});
        $must = [];
        foreach ($values as $value) {
            $must[] = [
                'term' => [
                    $attribute->code => $value,
                ],
            ];
        }

        return $must;
    }

    protected function buildQueryRangeAttributeFilters(FilterableAttribute $attribute): array
    {
        $values = array_filter(
            explode('-', $this->request->param->{$attribute->code}),
            fn($value) => is_numeric($value),
        );

        if (!count($values)) {
            return [];
        }

        $cond = [];
        if (isset($values[0])) {
            $cond['gte'] = $values[0];
        }
        if (isset($values[1])) {
            $cond['lte'] = $values[1];
        }

        return [
            [
                'range' => [
                    $this->getQueryField($attribute->code) => $cond,
                ],
            ],
        ];
    }

    protected function isRangeAttribute(FilterableAttribute $attribute): bool
    {
        return $attribute->frontendInput === 'price';
    }

    protected function getQueryField(string $attributeCode): string
    {
        if ($attributeCode === 'price') {
            return 'price_' . $this->session->customer->groupId . '_' . $this->context->scope;
        } else {
            return $attributeCode;
        }
    }

    protected function buildQueryAggregations(): array
    {
        $facets = [
            'price_bucket' => [
                'extended_stats' => ['field' => $this->getQueryField('price')],
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
            'currentPage' => $this->currentPage,
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
