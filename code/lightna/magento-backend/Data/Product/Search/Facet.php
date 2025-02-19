<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data\Product\Search;

use Lightna\Engine\Data\DataA;
use Lightna\Magento\Backend\Data\Product\Search\Facet\Option;

/**
 * @method string type(string $escapeMethod = null)
 * @method string code(string $escapeMethod = null)
 * @method string label(string $escapeMethod = null)
 * @method string position(string $escapeMethod = null)
 * @method string isInUse(string $escapeMethod = null)
 * @method string min(string $escapeMethod = null)
 * @method string max(string $escapeMethod = null)
 * @property Option[] $options
 */
class Facet extends DataA
{
    public string $type;
    public string $code;
    public string $label;
    public int $position;
    public bool $isInUse;
    public ?float $min = null;
    public ?float $max = null;
    public array $options = [];
}
