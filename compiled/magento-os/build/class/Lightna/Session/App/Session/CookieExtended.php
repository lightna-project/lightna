<?php

declare(strict_types=1);

namespace Lightna\Session\App\Session;

class CookieExtended extends \Lightna\Session\App\Session\Cookie
{
    protected function getOptions(): array
    {
        $plugins = [
            ['m', \Lightna\Magento\App\Plugin\App\Session\Cookie::class],
        ];

        $proceed = function () use (&$plugins, &$proceed) {
            if (!$callee = array_shift($plugins)) {
                return parent::getOptions();
            } else {
                $instance = getobj($callee[1]);
                if ($callee[0] === 'c') {
                    return $instance->getOptionsExtended()->bindTo($this, __CLASS__)($proceed);
                } else {
                    return $instance->getOptionsExtended($proceed);
                }
            }
        };

        return $proceed();
    }
}
