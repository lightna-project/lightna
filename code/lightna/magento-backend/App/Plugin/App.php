<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Plugin;

use Closure;
use Lightna\Engine\App\ObjectA;
use Lightna\Magento\Backend\Data\Config;

class App extends ObjectA
{
    protected Config $config;

    /** @noinspection PhpUnused */
    public function renderNoRouteExtended(): Closure
    {
        $config = $this->config;

        return function () use ($config) {
            $this->action = [
                'name' => 'page',
                'params' => [
                    'type' => 'cms',
                    'id' => $config->noRoutePageId,
                ],
            ];

            $this->processAction();
        };
    }
}
