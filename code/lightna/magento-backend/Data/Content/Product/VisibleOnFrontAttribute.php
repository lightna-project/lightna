<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data\Content\Product;

use Lightna\Engine\Data\DataA;

/**
 * @method string code(string $escapeMethod = null)
 * @method string label(string $escapeMethod = null)
 */
class VisibleOnFrontAttribute extends DataA
{
    public string $code;
    public string $label;
}
