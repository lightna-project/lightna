<?php

declare(strict_types=1);

namespace Lightna\AmastyLabel\Data\Product;

use Lightna\AmastyLabel\Data\Product\AmastyLabel\Label;
use Lightna\Engine\Data\DataA;

class AmastyLabel extends DataA
{
    public Label $product;
    public Label $category;
}
