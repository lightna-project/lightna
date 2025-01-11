<?php

declare(strict_types=1);

namespace Lightna\Magento\Staging\App\Plugin\Project;

use Closure;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Sql\AbstractPreparableSql;
use Lightna\Engine\App\ObjectA;
use Lightna\Magento\Staging\App\Query\Staging as Query;
use Lightna\Magento\Staging\App\Staging;

class Database extends ObjectA
{
    protected Staging $staging;
    protected Query $query;

    /** @noinspection PhpUnused */
    public function sqlExtended(Closure $proceed, AbstractPreparableSql &$sql): ResultInterface
    {
        if (!$this->isQueryRelevant($sql)) {
            return $proceed();
        }

        $sql = clone $sql;
        $this->staging->applyToQuery($sql);

        return $proceed();
    }

    protected function isQueryRelevant(AbstractPreparableSql $sql): bool
    {
        if (LIGHTNA_AREA !== 'backend') {
            return false;
        }
        if (!$this->query->isStagingEnabledForQuery($sql)) {
            // Clean to avoid garbage in Query::$disabledStaging array
            $this->query->cleanDisabledStaging($sql);

            return false;
        }

        return true;
    }
}
