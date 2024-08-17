<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console;

use Lightna\Engine\App\Bootstrap;

class CompileA extends CommandA
{
    protected function init(): void
    {
        $config = require LIGHTNA_ENTRY . 'config.php';
        require LIGHTNA_ENTRY . $config['src_dir'] . '/App/Bootstrap.php';
        require LIGHTNA_ENTRY . $config['src_dir'] . '/App/Compiler/ClassMap.php';
        Bootstrap::declaration($config);
    }

    protected function runSequence(array $sequence): void
    {
        foreach ($sequence as $item) {
            $this->runItem($item);
        }
    }

    protected function runItem(array $item): void
    {
        $this->printStart($item['message']);
        $compiler = is_object($item['compiler']) ? $item['compiler'] : getobj($item['compiler']);
        $compiler->make();
        $this->printEnd();
    }
}
