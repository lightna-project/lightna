<?php

declare(strict_types=1);

namespace Lightna\Engine;

class AppExtended extends \Lightna\Engine\App
{
    protected function process(): void
    {
        $plugins = [
            ['m', \Lightna\Session\App\Plugin\App::class],
        ];

        $proceed = function () use (&$plugins, &$proceed) {
            if (!$callee = array_shift($plugins)) {
                parent::process();
            } else {
                $instance = getobj($callee[1]);
                if ($callee[0] === 'c') {
                    $instance->processExtended()->bindTo($this, __CLASS__)($proceed);
                } else {
                    $instance->processExtended($proceed);
                }
            }
        };

        $proceed();
    }

    protected function renderNoRoute(): void
    {
        $plugins = [
            ['c', \Lightna\Magento\App\Plugin\App::class],
        ];

        $proceed = function () use (&$plugins, &$proceed) {
            if (!$callee = array_shift($plugins)) {
                parent::renderNoRoute();
            } else {
                $instance = getobj($callee[1]);
                if ($callee[0] === 'c') {
                    $instance->renderNoRouteExtended()->bindTo($this, __CLASS__)($proceed);
                } else {
                    $instance->renderNoRouteExtended($proceed);
                }
            }
        };

        $proceed();
    }
}
