<?php

declare(strict_types=1);

namespace Lightna\Engine\App\State;

use Lightna\Engine\App\Entity\State as StateEntity;
use Lightna\Engine\App\Exception\LightnaException;
use Lightna\Engine\App\State\Common\Index;
use Lightna\Engine\App\State\Common\Opcache;
use Lightna\Engine\App\State\Common\Session;
use Lightna\Engine\Data\DataA;

class Common extends DataA
{
    public Index $index;
    public Opcache $opcache;
    public Session $session;

    protected StateEntity $entity;

    protected function init(array $data = []): void
    {
        parent::init($this->entity->get('common'));
    }

    public function save(): void
    {
        if (LIGHTNA_AREA === 'frontend') {
            throw new LightnaException('State save is not available on Frontend');
        }

        $this->entity->set('common', o2a($this));
    }
}
