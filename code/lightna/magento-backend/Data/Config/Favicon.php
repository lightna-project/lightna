<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data\Config;

use Lightna\Engine\Data\DataA;
use Lightna\Engine\Data\Url;

/**
 * @method string href(string $escapeMethod = null)
 */
class Favicon extends DataA
{
    public string $href;

    protected Url $url;

    protected function init(array $data = []): void
    {
        parent::init($data);

        if (!str_starts_with($this->href, '/media')) {
            $this->href = $this->url->asset($this->href);
        }
    }
}
