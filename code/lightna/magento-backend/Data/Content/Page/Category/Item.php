<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data\Content\Page\Category;

use Lightna\Engine\Data\DataA;

/**
 * @method string entityId(string $escapeMethod = null)
 * @method string parentId(string $escapeMethod = null)
 * @method string name(string $escapeMethod = null)
 * @method string url(string $escapeMethod = null)
 */
class Item extends DataA
{
    public int $entityId;
    public int $parentId;
    public string $name;
    public string $url;
}
