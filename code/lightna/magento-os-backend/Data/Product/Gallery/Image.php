<?php

declare(strict_types=1);

namespace Lightna\Magento\Data\Product\Gallery;

use Lightna\Engine\Data\DataA;

class Image extends DataA
{
    public string $tile;
    public string $preview;
    public string $thumbnail;
    public string $max;

    public function tile(string $escapeMethod = null): string
    {
        return escape($this->cachedUrl($this->tile), $escapeMethod);
    }

    public function preview(string $escapeMethod = null): string
    {
        return escape($this->cachedUrl($this->preview), $escapeMethod);
    }

    public function thumbnail(string $escapeMethod = null): string
    {
        return escape($this->cachedUrl($this->thumbnail), $escapeMethod);
    }

    public function max(string $escapeMethod = null): string
    {
        return escape($this->max ? '/media/catalog/product/' . $this->max : '', $escapeMethod);
    }

    protected function cachedUrl(?string $path): string
    {
        return $path ? '/media/catalog/product/cache/' . $path : '';
    }
}
