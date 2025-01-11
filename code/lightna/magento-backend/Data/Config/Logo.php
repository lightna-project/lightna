<?php

declare(strict_types=1);

namespace Lightna\Magento\Data\Config;

use Lightna\Engine\Data\DataA;
use Lightna\Engine\Data\Url;

/**
 * @method string alt(string $escapeMethod = null)
 * @method string height(string $escapeMethod = null)
 * @method string src(string $escapeMethod = null)
 * @method string width(string $escapeMethod = null)
 */
class Logo extends DataA
{
    public string $alt;
    public string $src;
    public string|int $height;
    public string|int $width;

    protected Url $url;

    protected function init(array $data = []): void
    {
        parent::init($data);

        if (!str_starts_with($this->src, '/media')) {
            $this->src = $this->url->asset($this->src);
        }
    }
}
