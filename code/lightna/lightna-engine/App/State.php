<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Exception;
use Lightna\Engine\App\Entity\State as StateEntity;
use Lightna\Engine\App\State\Maintenance;
use Lightna\Engine\App\State\Opcache;
use Lightna\Engine\Data\DataA;

class State extends DataA
{
    public Maintenance $maintenance;
    public State\Index $index;
    public Opcache $opcache;

    protected StateEntity $entity;

    protected function init(array $data = []): void
    {
        parent::init($this->entity->get('frontend'));
    }

    public function save(): void
    {
        if (LIGHTNA_AREA === 'frontend') {
            throw new Exception('State save is not available on Frontend');
        }

        $this->entity->set('frontend', o2a($this));
    }
}
