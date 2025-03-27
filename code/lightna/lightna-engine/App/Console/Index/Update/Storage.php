<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Index\Update;

use Lightna\Engine\App\Exception\CliInputException;

class Storage extends UpdateA
{
    public function run(): void
    {
        $this->updateEntities(
            $this->getValidEntities(),
            (bool)$this->getOpt('multi'),
            (int)$this->getOpt('scope'),
        );
    }

    protected function getValidEntities(): array
    {
        $entities = [];
        $storages = $this->getValidStorages();
        foreach ($this->entities as $code => $entity) {
            if (!$this->indexer->getEntityIndex($code)) {
                continue;
            }
            if (in_array($entity['storage'], $storages)) {
                $entities[] = $code;
            }
        }

        return $entities;
    }

    protected function getValidStorages(): array
    {
        $storages = [];
        $codes = $this->getArgs();
        if (empty($codes)) {
            throw new CliInputException('Specify storage codes');
        }

        foreach ($codes as $code) {
            if (!isset($this->storages[$code])) {
                throw new CliInputException('Invalid storage code "' . $code . '"');
            }
            $storages[] = $code;
        }

        return $storages;
    }
}
