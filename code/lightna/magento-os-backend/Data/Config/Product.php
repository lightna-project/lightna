<?php

declare(strict_types=1);

namespace Lightna\Magento\Data\Config;

use Lightna\Engine\Data\DataA;
use Lightna\Magento\Data\Config\Product\Listing;

class Product extends DataA
{
    public Listing $listing;
}
