<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Index;

use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\Entity\Gc as EntityGc;

class Gc extends CommandA
{
    protected EntityGc $gc;

    public function run(): void
    {
        if ($this->getOpt('v')) {
            $this->gc->printKeys = true;
        }

        $this->gc->process();
        $this->renderStats();
    }

    protected function renderStats(): void
    {
        echo "Collected garbage:\n";

        foreach ($this->gc->getStats() as $entity => $stats) {
            $seconds = round($stats['time']);
            echo "    " . str_pad($entity, 30) . ' '
                . str_pad("{$stats['cleaned']} ", 8) . 'out of '
                . str_pad("{$stats['total']} ", 8) . "in {$seconds}s\n";
        }
    }
}
