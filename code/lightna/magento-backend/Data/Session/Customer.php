<?php

declare(strict_types=1);

namespace Lightna\Magento\Data\Session;

use Lightna\Engine\Data\DataA;

/**
 * @method string groupId(string $escapeMethod = null)
 */
class Customer extends DataA
{
    public string|int $groupId = 0;
}
