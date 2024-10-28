<?php

declare(strict_types=1);

namespace Lightna\Magento\AdobeStaging\App\Plugin\Project;

use Closure;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Sql\AbstractPreparableSql;
use Lightna\Engine\App\ObjectA;
use Lightna\Magento\AdobeStaging\App\Query\Staging as Query;
use Lightna\Magento\AdobeStaging\App\Staging;

class Database extends ObjectA
{
    protected Staging $staging;
    protected Query $query;

    public function sqlExtended(Closure $proceed, AbstractPreparableSql &$sql): ResultInterface
    {
        if (!$this->query->isStagingEnabledForQuery($sql)) {
            // Clean to avoid garbage in Query::$disabledStaging array
            $this->query->cleanDisabledStaging($sql);

            return $proceed();
        }

        $sql = clone $sql;
        $this->staging->applyToQuery($sql);

        return $proceed();
    }
}
