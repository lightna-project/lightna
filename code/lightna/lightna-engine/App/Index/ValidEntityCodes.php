<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Index;

use Lightna\Engine\App\Exception\CliInputException;
use Lightna\Engine\App\Indexer;
use Lightna\Engine\App\ObjectA;

class ValidEntityCodes extends ObjectA
{
    /** @AppConfig(entity) */
    protected array $entities;
    protected Indexer $indexer;

    public function get(array $args): array
    {
        $entities = [];
        if (empty($args)) {
            throw new CliInputException('Specify entity codes');
        }

        foreach ($args as $code) {
            if (!isset($this->entities[$code]) || !$this->indexer->getEntityIndex($code)) {
                throw new CliInputException('Invalid entity code "' . $code . '"');
            }
            $entities[] = $code;
        }

        return $entities;
    }
}
