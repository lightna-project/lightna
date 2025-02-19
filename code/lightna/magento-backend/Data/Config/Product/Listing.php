<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data\Config\Product;

use Lightna\Engine\Data\DataA;

/**
 * @method string defaultPageSize(string $escapeMethod = null)
 */
class Listing extends DataA
{
    public int $defaultPageSize;

    protected function init(array $data = []): void
    {
        settype($data['defaultPageSize'], 'int');
        parent::init($data);
    }
}
