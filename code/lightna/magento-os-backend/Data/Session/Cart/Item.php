<?php

declare(strict_types=1);

namespace Lightna\Magento\Data\Session\Cart;

use Lightna\Engine\Data\DataA;
use Lightna\Magento\Data\Product as ProductData;

/**
 * @method string productId(string $escapeMethod = null)
 * @method string sku(string $escapeMethod = null)
 * @method string name(string $escapeMethod = null)
 * @method string qty(string $escapeMethod = null)
 * @method string price(string $escapeMethod = null)
 */
class Item extends DataA
{
    public ProductData $product;
    public int $productId;
    public string $sku;
    public string $name;
    public int|float $qty;
    public string $price;

    /** @AppConfig(entity/product/entity) */
    protected string $productEntity;

    protected function defineProduct(): void
    {
        $data = getobj($this->productEntity)->get($this->productId);
        $this->product = newobj(ProductData::class, $data);
    }
}
