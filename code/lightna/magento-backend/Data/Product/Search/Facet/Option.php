<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data\Product\Search\Facet;

use Lightna\Engine\Data\DataA;

/**
 * @method string value(string $escapeMethod = null)
 * @method string label(string $escapeMethod = null)
 * @method string count(string $escapeMethod = null)
 */
class Option extends DataA
{
    public string|int $value;
    public string|int $label;
    public int $count;
    public bool $applied;
}
