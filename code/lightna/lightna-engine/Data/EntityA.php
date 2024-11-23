<?php

declare(strict_types=1);

namespace Lightna\Engine\Data;

/**
 * @method string title(string $escapeMethod = null)
 * @method string metaDescription(string $escapeMethod = null)
 */
class EntityA extends DataA
{
    public string $title;
    public string $metaDescription = '';
}
