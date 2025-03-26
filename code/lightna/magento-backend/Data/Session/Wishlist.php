<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data\Session;

use Lightna\Engine\Data\DataA;
use Lightna\Magento\Backend\Data\Session\Wishlist\Item;

/**
 * @property Item[] $items
 */
class Wishlist extends DataA
{
    public array $items;
}
