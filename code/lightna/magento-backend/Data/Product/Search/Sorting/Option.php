<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data\Product\Search\Sorting;

use Lightna\Engine\Data\DataA;

/**
 * @method string label(string $escapeMethod = null)
 */
class Option extends DataA
{
    public string $label;
    public array $params;
}
