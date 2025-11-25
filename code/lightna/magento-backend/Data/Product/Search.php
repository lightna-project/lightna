<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data\Product;

use Lightna\Engine\Data\DataA;
use Lightna\Engine\Data\Request;
use Lightna\Magento\Backend\App\Search as AppSearch;
use Lightna\Magento\Backend\Data\Product as ProductData;
use Lightna\Magento\Backend\Data\Product\Search\Facet as FacetData;
use Lightna\Magento\Backend\Data\Product\Search\Sorting\Option as SortingOption;

/**
 * @method string currentPage(string $escapeMethod = null)
 * @method string pageSize(string $escapeMethod = null)
 * @method string total(string $escapeMethod = null)
 * @property ProductData[] $result
 * @property FacetData[] $facets
 */
class Search extends DataA
{
    public int $currentPage;
    public int $pageSize;
    public int $total;
    public array $result;
    public array $facets;

    protected int $paginationMaxLinks = 5; // odd only and >=3
    protected array $sortingOptions;
    protected int $currentSortingOption;
    protected AppSearch $appSearch;
    protected Request $request;

    protected function init(array $data = []): void
    {
        parent::init($this->appSearch->search());
    }

    /**
     * @return SortingOption[]
     */
    public function getSortingOptions(): array
    {
        return $this->sortingOptions;
    }

    /** @noinspection PhpUnused */
    protected function defineSortingOptions(): void
    {
        $this->sortingOptions = [
            newobj(SortingOption::class, [
                'label' => 'Relevance',
                'params' => ['product_list_order' => null, 'product_list_dir' => null],
            ]),
            newobj(SortingOption::class, [
                'label' => 'Cheap',
                'params' => ['product_list_order' => 'price', 'product_list_dir' => null],
            ]),
            newobj(SortingOption::class, [
                'label' => 'Expensive',
                'params' => ['product_list_order' => 'price', 'product_list_dir' => 'desc'],
            ]),
        ];
    }

    public function getCurrentSortingOption(): int
    {
        return $this->currentSortingOption;
    }

    /** @noinspection PhpUnused */
    protected function defineCurrentSortingOption(): void
    {
        foreach ($this->sortingOptions as $i => $sortingOption) {
            $match = true;
            foreach ($sortingOption->params as $key => $value) {
                if ($this->request->param->{$key} !== $value) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                $this->currentSortingOption = $i;
                return;
            }
        }

        $this->currentSortingOption = -1;
    }

    public function getPagination(): array
    {
        $lastPage = (int)ceil($this->total / $this->pageSize);
        $currentPage = min($this->currentPage, $lastPage);
        $maxLinks = $this->paginationMaxLinks;

        /* 3 - persistent links (first, current, last) */
        /* 2 - left and right */
        $collapseTo = ($maxLinks - 3) / 2;

        if ($lastPage <= $maxLinks) {
            $collapseLeft = $collapseRight = false;
        } else {
            $collapseLeft = $currentPage > $collapseTo + 2; // 2 - first and current links
            $collapseRight = $lastPage - $currentPage >= $collapseTo + 2;
            if (!$collapseLeft) {
                $collapseTo = $collapseTo * 2 + 2 - $currentPage;
            }
            if (!$collapseRight) {
                $collapseTo = $collapseTo * 2 + 1 - ($lastPage - $currentPage);
            }
        }

        $pages = ['1' => '1'];
        if ($collapseLeft) {
            $firstLoop = true;
            for (
                $i = $currentPage - $collapseTo;
                $i < $currentPage && $i > 1;
                $i++
            ) {
                $pages[$i] = $firstLoop ? '...' : $i;
                $firstLoop = false;
            }
        } else {
            for ($i = 2; $i <= $currentPage; $i++) {
                $pages[$i] = $i;
            }
        }

        $pages[$currentPage] = $currentPage;

        if ($collapseRight) {
            $firstLoop = true;
            for (
                $i = $currentPage + $collapseTo;
                $i > $currentPage && $i < $lastPage;
                $i--
            ) {
                $pages[$i] = $firstLoop ? '...' : $i;
                $firstLoop = false;
            }
        } else {
            for ($i = $currentPage + 1; $i <= $lastPage; $i++) {
                $pages[$i] = $i;
            }
        }

        $pages[$lastPage] = $lastPage;

        ksort($pages);

        return $pages;
    }
}
