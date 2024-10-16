<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

class ScopeExtended extends \Lightna\Engine\App\Scope
{
    public function getList(): array
    {
        $plugins = [
            ['m', \Lightna\Magento\App\Plugin\App\Scope::class],
        ];

        $proceed = function () use (&$plugins, &$proceed) {
            if (!$callee = array_shift($plugins)) {
                return parent::getList();
            } else {
                $instance = getobj($callee[1]);
                if ($callee[0] === 'c') {
                    return $instance->getListExtended()->bindTo($this, __CLASS__)($proceed);
                } else {
                    return $instance->getListExtended($proceed);
                }
            }
        };

        return $proceed();
    }
}
