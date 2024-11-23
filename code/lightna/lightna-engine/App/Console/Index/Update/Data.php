<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Index\Update;

use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\Indexer;

class Data extends CommandA
{
    /** @AppConfig(entity) */
    protected array $entities;
    protected Indexer $indexer;

    public function run(): void
    {
        if ($this->getOpt(['list', 'l'])) {
            $this->printEntityCodes();
        } else {
            $this->indexer->validatePartialReindexBlock(true);
            $this->update();
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

    protected function update(): void
    {
        $whitelist = count($this->getArgs());
        foreach ($this->getEntities() as $code => $entity) {
            if ($whitelist && !$this->getArg($code)) {
                continue;
            }

            $this->printStart('index ' . $entity['storage'] . ' ' . $code);

            $this->indexer->reindex(
                $code,
                (int)$this->getOpt(['scope', 's']),
            );

            $stats = $this->indexer->stats;
            $this->printEnd($stats['count'] . ' items have been indexed in ' . $stats['time'] . ' seconds');
        }
    }
}
