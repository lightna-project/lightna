<?php

declare(strict_types=1);

namespace Lightna\Elasticsearch\App;

use Exception;
use Lightna\Engine\App\Context;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Search\Filter\FilterAbstract;
use Lightna\Engine\App\Search\Filter\FilterOption;
use Lightna\Engine\App\Search\Filter\FilterRange;
use Lightna\Engine\App\Search\SearchEngineInterface;

class Search extends ObjectA implements SearchEngineInterface
{
    protected Context $context;
    protected Client $client;
    /** @var FilterAbstract[] */
    protected array $filters;
    protected int $pageSize;
    protected int $currentPage;
    protected array $order;
    protected mixed $fieldMapper = null;

    public function setFilters(array $filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    public function setCurrentPage(int $page): static
    {
        $this->currentPage = $page;

        return $this;
    }

    public function setPageSize(int $size): static
    {
        $this->pageSize = $size;

        return $this;
    }

    public function setOrder(array $order): static
    {
        $this->order = $order;

        return $this;
    }

    public function setFieldMapper(callable $mapper): static
    {
        $this->fieldMapper = $mapper;

        return $this;
    }

    public function search(): array
    {
        $result = $this->client->search(
            $this->getIndexName('product'),
            $this->buildBody()
        );

        return $this->parseResult($result);
    }

    protected function getIndexName(string $entityName): string
    {
        return $entityName . '_' . $this->context->scope;
    }

    protected function buildBody(): array
    {
        return [
            'from' => ($this->currentPage - 1) * $this->pageSize,
            'size' => $this->pageSize,
            'stored_fields' => '_none_',
            'docvalue_fields' => ['_id', '_score'],
            'sort' => $this->buildSorting(),
            'query' => ['bool' => ['must' => array_values($this->buildFilters())]],
            'aggregations' => $this->buildFacets(),
        ];
    }

    protected function buildSorting(): array
    {
        if (!$this->order) {
            return [];
        }

        return [[$this->getFieldName($this->order[0]) => ['order' => $this->order[1]]]];
    }

    protected function buildFilters(): array
    {
        $filters = [];
        foreach ($this->filters as $filter) {
            if ($this->isFilterEmpty($filter)) {
                continue;
            }

            foreach ($this->buildFilter($filter) as $must) {
                $filters[$filter->code] = $must;
            }
        }

        return $filters;
    }

    protected function isFilterEmpty(FilterAbstract $filter): bool
    {
        if ($filter instanceof FilterRange) {
            return empty($filter->from) && empty($filter->to);
        } elseif ($filter instanceof FilterOption) {
            return empty($filter->values);
        } else {
            throw new Exception('The filter type "' . get_class($filter) . '" is not mapped.');
        }
    }

    protected function buildFilter(FilterAbstract $filter): array
    {
        if ($filter instanceof FilterRange) {
            return $this->buildFilterRange($filter);
        } elseif ($filter instanceof FilterOption) {
            return $this->buildFilterOption($filter);
        }

        return [];
    }

    protected function buildFilterOption(FilterOption $filter): array
    {
        return [[
            'terms' => [
                $filter->code => $filter->values,
            ],
        ]];
    }

    protected function buildFilterRange(FilterRange $filter): array
    {
        $cond = [];
        if (isset($filter->from)) {
            $cond['gte'] = $filter->from;
        }
        if (isset($filter->to)) {
            $cond['lte'] = $filter->to;
        }

        return [['range' => [
            $this->getFieldName($filter->code) => $cond,
        ]]];
    }

    protected function getFieldName(string $code): string
    {
        return $this->fieldMapper ? call_user_func($this->fieldMapper, $code) : $code;
    }

    protected function buildFacets(): array
    {
        $filters = $this->buildFilters();
        $aggs = [];
        foreach ($this->filters as $filter) {
            if (!$filter->isFacetable) {
                continue;
            }

            $key = $filter->code . '_bucket';
            $aggFilter = $filters;
            unset($aggFilter[$filter->code]);

            $aggs[$key] = $this->buildFacet($filter, $aggFilter);
        }

        return $aggs;
    }

    protected function buildFacet(FilterAbstract $filter, array $aggFilter = []): array
    {
        if ($filter instanceof FilterRange) {
            return $this->buildFacetRange($filter);
        } elseif ($filter instanceof FilterOption) {
            return $this->buildFacetOption($filter, $aggFilter);
        } else {
            throw new Exception('The filter type "' . get_class($filter) . '" is not mapped.');
        }
    }

    protected function buildFacetRange(FilterRange $filter): array
    {
        return [
            'extended_stats' => ['field' => $this->getFieldName($filter->code)],
        ];
    }

    protected function buildFacetOption(FilterOption $filter, array $aggFilter = []): array
    {
        return [
            'global' => (object)[],
            'aggs' => [
                'filtered' => [
                    'filter' => ['bool' => ['filter' => array_values($aggFilter)]],
                    'aggs' => [$filter->code => ['terms' => ['field' => $this->getFieldName($filter->code), 'size' => 500]]],
                ],
            ],
        ];
    }

    protected function parseResult(array $result): array
    {
        return [
            'total' => $result['hits']['total']['value'],
            'currentPage' => $this->currentPage,
            'pageSize' => $this->pageSize,
            'ids' => $this->parseResultIds($result),
            'facets' => $this->parseResultFacets($result),
        ];
    }

    protected function parseResultIds(array $result): array
    {
        $ids = [];
        foreach ($result['hits']['hits'] as $hit) {
            $ids[] = $hit['fields']['_id'][0];
        }

        return $ids;
    }

    protected function parseResultFacets(array $result): array
    {
        $facets = [];
        $position = 0;
        foreach ($result['aggregations'] as $key => $agg) {
            $code = preg_replace('~_bucket$~', '', $key);
            $agg = $agg['filtered'][$code] ?? $agg;
            if (empty($agg['min']) && empty($agg['buckets'])) {
                continue;
            }

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
                        'value' => (string)$bucket['key'],
                        'count' => $bucket['doc_count'],
                    ];
                }
                $facet = merge($facet, [
                    'type' => 'option',
                    'options' => $options,
                ]);
            }

            $this->markApplied($facet, $hasAppliedOptions);
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

        /** @var FilterOption $filter */
        $filter = $this->filters[$facet['code']];
        if (!$filter instanceof FilterOption) {
            return;
        }

        foreach ($facet['options'] as $i => $option) {
            $isApplied = in_array($option['value'], $filter->values);
            $facet['options'][$i]['applied'] = $isApplied;
            $hasAppliedOptions = $hasAppliedOptions || $isApplied;
        }
    }
}
