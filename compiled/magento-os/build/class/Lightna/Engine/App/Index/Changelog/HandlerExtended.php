<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Index\Changelog;

class HandlerExtended extends \Lightna\Engine\App\Index\Changelog\Handler
{
    protected function addIndexBatchDependencies(array &$indexBatch): void
    {
        $plugins = [
            ['m', \Lightna\Magento\App\Plugin\App\Index\Changelog\Handler::class],
        ];

        $proceed = function () use (&$plugins, &$proceed, &$indexBatch) {
            if (!$callee = array_shift($plugins)) {
                parent::addIndexBatchDependencies($indexBatch);
            } else {
                $instance = getobj($callee[1]);
                if ($callee[0] === 'c') {
                    $instance->addIndexBatchDependenciesExtended()->bindTo($this, __CLASS__)($proceed, $indexBatch);
                } else {
                    $instance->addIndexBatchDependenciesExtended($proceed, $indexBatch);
                }
            }
        };

        $proceed();
    }
}
