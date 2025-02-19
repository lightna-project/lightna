<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data\Content\Page\Menu;

use Lightna\Engine\Data\DataA;

/**
 * @property Item[] children
 * @method string entityId(string $escapeMethod = null)
 * @method string level(string $escapeMethod = null)
 * @method string name(string $escapeMethod = null)
 * @method string url(string $escapeMethod = null)
 */
class Item extends DataA
{
    public array $children;
    public int $entityId;
    public int $level;
    public string $name;
    public string $url;
}
