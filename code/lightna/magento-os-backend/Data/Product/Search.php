<?php

declare(strict_types=1);

namespace Lightna\Magento\Data\Product;

use Lightna\Engine\Data\DataA;
use Lightna\Magento\App\Search as AppSearch;
use Lightna\Magento\Data\Product as ProductData;
use Lightna\Magento\Data\Product\Search\Facet as FacetData;

/**
 * @method string total(string $escapeMethod = null)
 * @method string pageSize(string $escapeMethod = null)
 * @property ProductData[] $result
 * @property FacetData[] $facets
 */
class Search extends DataA
{
    public int $total;
    public int $pageSize;
    public array $result;
    public array $facets;

    protected AppSearch $appSearch;

    protected function init($data = []): void
    {
        parent::init($this->appSearch->search());
    }
}
