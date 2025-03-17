<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data\Product\Breadcrumbs;

use Lightna\Engine\Data\DataA;

/**
 * @method string name(string $escapeMethod = null)
 * @method string url(string $escapeMethod = null)
 */
class Breadcrumb extends DataA
{
    public string $name;
    public string $url;
}
