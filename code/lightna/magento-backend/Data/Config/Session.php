<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data\Config;

use Lightna\Engine\Data\DataA;
use Lightna\Magento\Backend\Data\Config\Session\Cookie;

class Session extends DataA
{
    public Cookie $cookie;
}
