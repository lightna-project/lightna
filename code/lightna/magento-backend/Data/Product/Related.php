<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data\Product;

use Lightna\Engine\Data\DataA;
use Lightna\Magento\Backend\App\Entity\Product as ProductEntity;
use Lightna\Magento\Backend\Data\Product;

class Related extends DataA
{
    /** @var Product[] */
    public array $products;

    protected Product $product;
    protected ProductEntity $productEntity;

    protected int $limit = 20;


    /** @noinspection PhpUnused */
    protected function defineProducts(): void
    {
        $this->products = [];
        $productIds = array_slice($this->product->related, 0, $this->limit);
        foreach ($this->productEntity->getList($productIds) as $productId => $productData) {
            if (!$productData) {
                continue;
            }

            $this->products[$productId] = newobj(
                Product::class,
                $productData,
            );
        }
    }
}
