<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Deploy;

use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\Indexer;

class Data extends CommandA
{
    /** @AppConfig(entity) */
    protected array $entities;
    protected Indexer $indexer;

    public function run(): void
    {
        if ($this->getArg(['--list', '-l'])) {
            $this->printEntityCodes();
        } else {
            $this->deploy();
        }
    }

    protected function getEntities(): array
    {
        $entities = [];
        foreach ($this->entities as $code => $entity) {
            if ($entity['storage'] === 'opcache' || !isset($entity['index'])) {
                continue;
            }
            $entities[$code] = $entity;
        }

        return $entities;
    }

    protected function printEntityCodes(): void
    {
        foreach ($this->getEntities() as $code => $entity) {
            echo "    $code\n";
        }
    }

    protected function deploy(): void
    {
        $whitelist = $this->hasCommands();
        foreach ($this->getEntities() as $code => $entity) {
            if ($whitelist && !$this->getArg($code)) {
                continue;
            }

            $this->printStart('index ' . $entity['storage'] . ' ' . $code);

            $this->indexer->reindex(
                $code,
                (int)$this->getArg('--scope'),
            );

            $stats = $this->indexer->stats;
            $this->printEnd($stats['count'] . ' items have been indexed in ' . $stats['time'] . ' seconds');
        }
    }
}
