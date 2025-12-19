<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Search;

use Exception;
use Lightna\Elasticsearch\App\Search as ElasticSearch;
use Lightna\Engine\App\Context;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Search\Filter\FilterAbstract;
use Lightna\Engine\App\Search\Filter\FilterOption;
use Lightna\Engine\App\Search\Filter\FilterRange;
use Lightna\Engine\App\Search\SearchEngineInterface;
use Lightna\Engine\Data\Request;
use Lightna\Magento\Backend\App\Entity\Product as ProductEntity;
use Lightna\Magento\Backend\Data\Category;
use Lightna\Magento\Backend\Data\Config;
use Lightna\Magento\Backend\Data\Content\Category as CategoryContent;
use Lightna\Magento\Backend\Data\Content\Product\FilterableAttribute;
use Lightna\Magento\Backend\Data\Session;

class Adapter extends ObjectA
{
    protected SearchEngineInterface $searchEngine;
    protected Config $config;
    protected Context $context;
    protected Category $category;
    protected Session $session;
    protected CategoryContent $categoryContent;
    protected Request $request;
    protected int $pageSize;
    protected int $currentPage;

    public function search(): array
    {
        $this->configureSearchEngine();
        $result = $this->searchEngine->search();

        return $this->decorateResult($result);
    }

    /** @noinspection PhpUnused */
    protected function defineSearchEngine(): void
    {
        // Extension point

        $this->searchEngine = getobj(ElasticSearch::class);
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

    protected function configureSearchEngine(): void
    {
        $this->searchEngine
            ->setFilters($this->getFilters())
            ->setCurrentPage($this->getCurrentPage())
            ->setPageSize($this->getPageSize())
            ->setOrder($this->getOrder())
            ->setFieldMapper([$this, 'fieldMapper']);
    }

    protected function getFilters(): array
    {
        $filters = $this->getPermanentFilters();
        foreach ($this->categoryContent->filterableAttributes as $attribute) {
            $filters[$attribute->code] = $this->createFilter($attribute);
        }

        return $filters;
    }

    protected function getPermanentFilters(): array
    {
        return [
            'category_ids' => newobj(FilterOption::class, [
                'code' => 'category_ids',
                'values' => [$this->category->entityId],
                'isFacetable' => false,
            ]),
            'visibility' => newobj(FilterOption::class, [
                'code' => 'visibility',
                'values' => [2, 4],
                'isFacetable' => false,
            ]),
        ];
    }

    protected function createFilter(FilterableAttribute $attribute): FilterAbstract
    {
        return match ($filterType = $this->getAttributeFilterType($attribute)) {
            FilterRange::class => $this->createFilterRange($attribute->code),
            FilterOption::class => $this->createFilterOption($attribute->code),
            default => throw new Exception('The filter type "' . $filterType . '" is not mapped.'),
        };
    }

    protected function createFilterRange(string $code): FilterRange
    {
        return newobj(
            FilterRange::class,
            $this->getFilterRangeData($code),
        );
    }

    protected function getFilterRangeData(string $code): array
    {
        $raw = $this->request->param->$code;
        $from = $to = null;

        if (!is_null($raw) && $raw !== '') {
            $values = array_filter(
                explode('-', $this->request->param->$code ?? ''),
                fn($value) => is_numeric($value),
            );

            $from = isset($values[0]) ? (float)$values[0] : null;
            $to = isset($values[1]) ? (float)$values[1] : null;
        }

        return [
            'code' => $code,
            'from' => $from,
            'to' => $to,
        ];
    }

    protected function createFilterOption(string $code): FilterOption
    {
        return newobj(
            FilterOption::class,
            $this->getFilterOptionData($code),
        );
    }

    protected function getFilterOptionData(string $code): array
    {
        $raw = $this->request->param->$code;
        if (is_null($raw) || $raw === '') {
            $values = [];
        } else {
            $values = explode('_', $raw);
        }

        return [
            'code' => $code,
            'values' => $values,
        ];
    }

    protected function getAttributeFilterType(FilterableAttribute $attribute): string
    {
        return $attribute->frontendInput === 'price'
            ? FilterRange::class
            : FilterOption::class;
    }

    protected function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    protected function getPageSize(): int
    {
        return $this->pageSize;
    }

    protected function getOrder(): array
    {
        $order = $this->request->param->product_list_dir === 'desc' ? 'desc' : 'asc';

        return match ($this->request->param->product_list_order) {
            'price' => ['price', $order],
            default => ['position', 'asc'],
        };
    }

    public function fieldMapper(string $code): string
    {
        if ($code === 'price') {
            return 'price_' . $this->session->customer->groupId . '_' . $this->context->scope;
        } elseif ($code === 'position') {
            return 'position_category_' . $this->category->entityId;
        } else {
            return $code;
        }
    }

    protected function decorateResult(array $result): array
    {
        $this->loadItems($result);
        $this->labelFacets($result);

        return $result;
    }

    protected function loadItems(array &$result): void
    {
        $items = [];
        $entity = getobj(ProductEntity::class);
        foreach ($entity->getList($result['ids']) as $data) {
            if (!empty($data)) {
                $items[] = $data;
            }
        }

        $result['items'] = $items;
    }

    protected function labelFacets(array &$result): void
    {
        foreach ($result['facets'] as &$facet) {
            $this->labelFacet($facet);
        }
    }

    protected function labelFacet(array &$facet): void
    {
        if ($attr = $this->categoryContent->filterableAttributes[camel($facet['code'])] ?? null) {
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
