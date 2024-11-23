<?php

declare(strict_types=1);

namespace Lightna\Magento\AdobeStaging\App\Plugin\Index;

use Closure;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;
use Lightna\Magento\AdobeStaging\App\Query\Staging as StagingQuery;
use Lightna\Magento\AdobeStaging\App\Staging;

class EntityLink extends ObjectA
{
    protected Database $db;
    protected Staging $staging;
    protected StagingQuery $stagingQuery;

    /** @noinspection PhpUnused */
    public function getColumnExtended(Closure $proceed, string $table): string
    {
        return $this->staging->getTableParent($table) ? 'row_id' : $proceed();
    }

    /** @noinspection PhpUnused */
    public function getIdsExtended(Closure $proceed, string $table, array $ids): array
    {
        return $this->staging->getTableParent($table)
            ? $this->stagingQuery->convertRowIdsToEntityIds($table, $ids)
            : $proceed();
    }
}
