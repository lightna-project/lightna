<?php

declare(strict_types=1);

namespace Lightna\Magento\Data\Session\Cart;

use Lightna\Engine\Data\DataA;

/**
 * @method productId(string $escapeMethod = null)
 * @method sku(string $escapeMethod = null)
 * @method name(string $escapeMethod = null)
 * @method qty(string $escapeMethod = null)
 * @method price(string $escapeMethod = null)
 */
class Item extends DataA
{
    public string|int $productId;
    public string $sku;
    public string $name;
    public int|float $qty;
    public string $price;
}
