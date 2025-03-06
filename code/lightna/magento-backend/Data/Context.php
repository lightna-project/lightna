<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data;

use Lightna\Engine\Data\DataA;
use Lightna\Magento\Backend\Data\Context\Cookie;

class Context extends DataA
{
    public Cookie $cookie;
}
