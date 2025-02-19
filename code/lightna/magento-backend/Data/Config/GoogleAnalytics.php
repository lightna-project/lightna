<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data\Config;

use Lightna\Engine\Data\DataA;

/**
 * @method string account(string $escapeMethod = null)
 */
class GoogleAnalytics extends DataA
{
    public bool $active;
    public ?string $account = null;

    protected function init(array $data = []): void
    {
        settype($data['active'], 'bool');

        parent::init($data);
    }
}
