<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Index;

use Lightna\Engine\App\Index\IndexAbstract;
use Lightna\Magento\Backend\App\Entity\Product as ProductEntity;
use Lightna\Magento\Backend\App\Query\Product as ProductQuery;
use Lightna\Magento\Backend\App\Query\Url;
use Lightna\Magento\Backend\Index\Provider\Product as ProductDataProvider;

class Product extends IndexAbstract
{
    protected ProductEntity $entity;
    protected ProductQuery $product;
    protected Url $url;
    protected bool $hasRoutes = true;

    public function getDataBatch(array $ids): array
    {
        return $this->getDataProvider()->getData($ids);
    }

    public function getRoutesBatch(array $ids): array
    {
        return $this->url->getEntityRoutesBatch('product', $ids);
    }

    protected function getDataProvider(): ProductDataProvider
    {
        return newobj(ProductDataProvider::class);
    }

    public function scan(string|int $lastId = null): array
    {
        return $this->product->getAvailableIdsBatch(1000, $lastId);
    }

    public function gcCheck(array $ids): array
    {
        $exists = $this->product->getAvailableIds($ids);

        return array_diff($ids, $exists);
    }
}
