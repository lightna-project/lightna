<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Index;

use Lightna\Engine\App\Indexer;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\UserException;

class ValidEntityCodes extends ObjectA
{
    /** @AppConfig(entity) */
    protected array $entities;
    protected Indexer $indexer;

    public function get(array $args): array
    {
        $entities = [];
        if (empty($args)) {
            throw new UserException('Specify entity codes');
        }

        foreach ($args as $code) {
            if (!isset($this->entities[$code]) || !$this->indexer->getEntityIndex($code)) {
                throw new UserException('Invalid entity code "' . $code . '"');
            }
            $entities[] = $code;
        }

        return $entities;
    }
}
