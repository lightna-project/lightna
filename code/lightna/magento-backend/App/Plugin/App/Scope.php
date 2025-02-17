<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Plugin\App;

use Lightna\Engine\App\ObjectA;
use Lightna\Magento\App\Scope as MagentoScope;

class Scope extends ObjectA
{
    protected MagentoScope $magentoScope;

    /** @noinspection PhpUnused */
    public function getListExtended(): array
    {
        return $this->magentoScope->getList();
    }
}
