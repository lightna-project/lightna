<?php

declare(strict_types=1);

namespace Lightna\Magento\AdobeStaging\App\Query;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;
use Lightna\Magento\AdobeStaging\App\Staging as AppStaging;

class Staging extends ObjectA
{
    protected Database $db;
    protected AppStaging $appStaging;

    public function getVersionId(): int
    {
        $version = 1;
        if ($data = $this->fetchFlag('staging')) {
            $version = (int)json_decode($data, true)['current_version'];
        }

        return $version;
    }

    protected function fetchFlag(string $flag): ?string
    {
        return $this->db->fetchOneCol($this->getFlagSelect($flag));
    }

    protected function getFlagSelect(string $flag): Select
    {
        return $this->db->select()
            ->columns(['flag_data'])
            ->from('flag')
            ->where(['flag_code = ?' => $flag]);
    }

    public function convertRowIdsToEntityIds(string $table, array $ids): array
    {
        $parent = $this->appStaging->getTableParent($table);
        $column = $this->appStaging->getEntityIdColumn($parent);

        return $this->db->fetchCol($this->getEntityIdsByRowIdsSelect($parent, $column, $ids));
    }

    protected function getEntityIdsByRowIdsSelect(
        string $mainTable,
        string $entityIdColumn,
        array $rowIds
    ): Select {
        $select = $this->db->select(['e' => $mainTable])
            ->columns([$entityIdColumn]);
        $select->where->in('row_id', $rowIds);

        return $select;
    }
}
