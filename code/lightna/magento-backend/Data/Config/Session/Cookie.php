<?php

declare(strict_types=1);

namespace Lightna\Magento\Data\Config\Session;

use Lightna\Engine\Data\DataA;

class Cookie extends DataA
{
    public int $lifetime;

    protected function init(array $data = []): void
    {
        settype($data['lifetime'], 'int');

        parent::init($data);
    }
}
