<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Console\Entity;

use Lightna\Engine\App\Console\CommandA;
use Lightna\Engine\App\Context;
use Lightna\Engine\App\UserException;

class Show extends CommandA
{
    /** @AppConfig(entity) */
    protected array $entities;
    protected Context $context;

    public function run(): void
    {
        $entity = $this->getArg(1);
        if (!isset($this->entities[$entity])) {
            throw new UserException('Unknown entity "' . $entity . '"');
        }

        if (!$class = $this->entities[$entity]['entity'] ?? null) {
            throw new UserException('Class for entity "' . $entity . '" not defined');
        }

        if (!$id = $this->getArg(2)) {
            throw new UserException('Specify entity ID');
        }

        $scope = (int)$this->getOpt(['scope', 's']);
        if ($scope === 0) {
            throw new UserException('Specify scope');
        }

        $this->context->scope = $scope;
        echo json_pretty(getobj($class)->get($id)) . "\n";
    }
}
