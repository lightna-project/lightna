<?php

declare(strict_types=1);

namespace Lightna\Magento\Data\Product\Gallery;

use Lightna\Engine\Data\DataA;

/**
 * @method string tile(string $escapeMethod = null)
 * @method string preview(string $escapeMethod = null)
 * @method string thumbnail(string $escapeMethod = null)
 * @method string max(string $escapeMethod = null)
 */
class Image extends DataA
{
    public string $tile;
    public string $preview;
    public string $thumbnail;
    public string $max;

    protected function init(array $data = []): void
    {
        foreach ($this->getCachedUrlFields() as $field) {
            $data[$field] = $this->cachedUrl($data[$field]);
        }

        parent::init($data);
    }

    protected function getCachedUrlFields(): array
    {
        return ['tile', 'preview', 'thumbnail', 'max'];
    }

    protected function cachedUrl(?string $path): string
    {
        return $path ? '/media/catalog/product/cache/' . $path : '';
    }
}
