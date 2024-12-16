<?php

declare(strict_types=1);

namespace Lightna\Engine\App\State;

use Exception;
use Lightna\Engine\App\Entity\State as StateEntity;
use Lightna\Engine\App\State\Common\Index;
use Lightna\Engine\App\State\Common\Maintenance;
use Lightna\Engine\App\State\Common\Opcache;
use Lightna\Engine\Data\DataA;

class Common extends DataA
{
    public Maintenance $maintenance;
    public Index $index;
    public Opcache $opcache;

    protected StateEntity $entity;

    protected function init(array $data = []): void
    {
        parent::init($this->entity->get('common'));
    }

    public function save(): void
    {
        if (LIGHTNA_AREA === 'frontend') {
            throw new Exception('State save is not available on Frontend');
        }

        $this->entity->set('common', o2a($this));
    }
}
