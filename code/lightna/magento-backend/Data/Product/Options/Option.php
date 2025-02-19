<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data\Product\Options;

use Lightna\Engine\Data\DataA;

/**
 * @method string id(string $escapeMethod = null)
 * @method string label(string $escapeMethod = null)
 * @method string attributeCode(string $escapeMethod = null)
 */
class Option extends DataA
{
    public int $id;
    public string $label;
    public string $attributeCode;
    public bool $selected;
    public bool $available;
}
