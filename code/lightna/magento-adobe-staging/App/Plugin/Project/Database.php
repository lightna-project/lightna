<?php

declare(strict_types=1);

namespace Lightna\Magento\AdobeStaging\App\Plugin\Project;

use Closure;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Sql\AbstractPreparableSql;
use Lightna\Engine\App\ObjectA;
use Lightna\Magento\AdobeStaging\App\Staging;

class Database extends ObjectA
{
    protected Staging $staging;

    public function sqlExtended(Closure $proceed, AbstractPreparableSql &$sql): ResultInterface
    {
        $sql = clone $sql;
        $this->staging->apply($sql);

        return $proceed();
    }
}
