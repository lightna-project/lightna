<?php

declare(strict_types=1);

namespace Lightna\AmastyLabel\Data\Product\AmastyLabel;

use Lightna\Engine\Data\DataA;

/**
 * @method string image(string $escapeMethod = null)
 */
class Label extends DataA
{
    public string $image = '';
    public int $position = 0;
}
