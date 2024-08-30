<?php

declare(strict_types=1);

namespace Lightna\Magento\Data\Session;

use Lightna\Engine\Data\DataA;

/**
 * @method string groupId(string $escapeMethod = null)
 */
class User extends DataA
{
    public string|int $groupId;

    protected function init($data = []): void
    {
        parent::init($data);
        $this->groupId ??= 0;
    }
}
