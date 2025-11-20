<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data\Product\Search;

use Lightna\Engine\Data\DataA;
use Lightna\Engine\Data\Request;
use Lightna\Magento\Backend\Data\Product\Search\Facet\Option;

/**
 * @method string type(string $escapeMethod = null)
 * @method string code(string $escapeMethod = null)
 * @method string label(string $escapeMethod = null)
 * @method string position(string $escapeMethod = null)
 * @method string isInUse(string $escapeMethod = null)
 * @method string min(string $escapeMethod = null)
 * @method string max(string $escapeMethod = null)
 * @method string currMin(string $escapeMethod = null)
 * @method string currMax(string $escapeMethod = null)
 * @property Option[] $options
 */
class Facet extends DataA
{
    public string $type;
    public string $code;
    public string $label;
    public int $position;
    public bool $isInUse;
    public float $min;
    public float $max;
    public ?float $currMin;
    public ?float $currMax;
    public array $options = [];

    protected Request $request;

    /** @noinspection PhpUnused */
    protected function defineCurrMin(): void
    {
        $this->defineCurrMinMax();
    }

    /** @noinspection PhpUnused */
    protected function defineCurrMax(): void
    {
        $this->defineCurrMinMax();
    }

    protected function defineCurrMinMax(): void
    {
        $parts = array_filter(
            explode('-', $this->request->param->{$this->code} ?? ''),
            fn($value) => is_numeric($value),
        );

        $min = $parts[0] ?? null;
        $max = $parts[1] ?? null;

        $this->currMin = is_numeric($min) ? (float)$min : null;
        $this->currMax = is_numeric($max) ? (float)$max : null;
    }
}
