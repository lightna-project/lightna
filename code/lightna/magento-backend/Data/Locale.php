<?php

declare(strict_types=1);

namespace Lightna\Magento\Data;

use Lightna\Engine\Data\DataA;

/**
 * @method string code(string $escapeMethod = null)
 * @method string lang(string $escapeMethod = null)
 */
class Locale extends DataA
{
    public string $code;
    public string $lang;
}
