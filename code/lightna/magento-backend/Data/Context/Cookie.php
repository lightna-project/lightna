<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data\Context;

use Lightna\Engine\Data\DataA;
use Lightna\Magento\Backend\Data\Config;

class Cookie extends DataA
{
    public int $lifetime;

    protected Config $config;

    /** @noinspection PhpUnused */
    protected function defineLifetime(): void
    {
        $this->lifetime = $this->config->session->cookie->lifetime;
    }
}
