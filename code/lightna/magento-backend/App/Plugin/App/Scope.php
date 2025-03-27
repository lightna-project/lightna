<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Plugin\App;

use Lightna\Engine\App\ObjectA;
use Lightna\Magento\Backend\App\Scope as MagentoScope;

class Scope extends ObjectA
{
    protected MagentoScope $magentoScope;

    /**
     * @see          \Lightna\Engine\App\Scope::getList()
     * @noinspection PhpUnused
     */
    public function getListExtended(): array
    {
        return $this->magentoScope->getList();
    }
}
