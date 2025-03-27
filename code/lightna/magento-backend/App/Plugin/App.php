<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Plugin;

use Closure;
use Lightna\Engine\App\ObjectA;
use Lightna\Magento\Backend\Data\Config;

class App extends ObjectA
{
    protected Config $config;

    /**
     * @see          \Lightna\Engine\App::defineNoRouteAction()
     * @noinspection PhpUnused
     */
    public function defineNoRouteActionExtended(Closure $proceed): Closure
    {
        $config = $this->config;

        return function () use ($proceed, $config) {
            $this->noRouteAction = [
                'name' => 'page',
                'params' => [
                    'type' => 'cms',
                    'id' => $config->noRoutePageId,
                ],
            ];

            $proceed();
        };
    }
}
