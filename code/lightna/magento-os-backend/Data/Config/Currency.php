<?php

declare(strict_types=1);

namespace Lightna\Magento\Data\Config;

use Lightna\Engine\Data\DataA;

/**
 * @method string default(string $escapeMethod = null)
 */
class Currency extends DataA
{
    public string $default;
}
