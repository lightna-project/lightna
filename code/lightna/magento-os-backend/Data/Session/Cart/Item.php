<?php

declare(strict_types=1);

namespace Lightna\Magento\Data\Session\Cart;

use Lightna\Engine\Data\DataA;

/**
 * @method string productId(string $escapeMethod = null)
 * @method string sku(string $escapeMethod = null)
 * @method string name(string $escapeMethod = null)
 * @method string qty(string $escapeMethod = null)
 * @method string price(string $escapeMethod = null)
 */
class Item extends DataA
{
    public string|int $productId;
    public string $sku;
    public string $name;
    public int|float $qty;
    public string $price;
}
