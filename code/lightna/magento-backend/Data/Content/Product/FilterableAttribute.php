<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data\Content\Product;

use Lightna\Engine\Data\DataA;

/**
 * @method string code(string $escapeMethod = null)
 * @method string label(string $escapeMethod = null)
 * @method string backendType(string $escapeMethod = null)
 * @method string frontendInput(string $escapeMethod = null)
 */
class FilterableAttribute extends DataA
{
    public string $code;
    public string $label;
    public string $backendType;
    public string $frontendInput;
    public array $options;
}
