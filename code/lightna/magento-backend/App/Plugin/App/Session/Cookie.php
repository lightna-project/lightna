<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Plugin\App\Session;

use Lightna\Engine\App\ObjectA;
use Lightna\Magento\Data\Config;

class Cookie extends ObjectA
{
    protected Config $config;

    /** @noinspection PhpUnused */
    public function getOptionsExtended(\Closure $proceed): array
    {
        $options = $proceed();
        $lifetime = $this->config->session->cookie->lifetime;
        $options['expires'] = $lifetime > 0 ? time() + $lifetime : $lifetime;

        return $options;
    }
}
