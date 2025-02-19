<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data\Session;

use Lightna\Engine\Data\DataA;
use Lightna\Magento\Backend\Data\Session\Cart\Item;

/**
 * @property Item[] $items
 * @method string qty(string $escapeMethod = null)
 * @method string grandTotal(string $escapeMethod = null)
 */
class Cart extends DataA
{
    public array $items;
    public int $qty = 0;
    public float $grandTotal;
}
