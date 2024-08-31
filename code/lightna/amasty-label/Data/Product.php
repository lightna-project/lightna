<?php

declare(strict_types=1);

namespace Lightna\AmastyLabel\Data;

use Lightna\AmastyLabel\Data\Product\AmastyLabel;
use Lightna\Magento\Data\Product as MagentoProduct;

class Product extends MagentoProduct
{
    public AmastyLabel $amastyLabel;
}
