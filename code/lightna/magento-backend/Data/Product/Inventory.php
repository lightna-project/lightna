<?php

declare(strict_types=1);

namespace Lightna\Magento\Data\Product;

use Lightna\Engine\Data\DataA;

/**
 * @method string qty(string $escapeMethod = null)
 * @method string status(string $escapeMethod = null)
 * @method string backorders(string $escapeMethod = null)
 */
class Inventory extends DataA
{
    public float $qty;
    public bool $status;
    public bool $backorders;
}
