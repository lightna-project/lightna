<?php

declare(strict_types=1);

namespace Lightna\Magento\AdobeStaging\App\Query;

use Laminas\Db\Sql\AbstractPreparableSql;
use Laminas\Db\Sql\Select;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;
use Lightna\Magento\AdobeStaging\App\Staging as AppStaging;

class Staging extends ObjectA
{
    public const PREVIOUS_VERSION_FLAG = 'lightna_staging_previous_version';
    protected Database $db;
    protected AppStaging $appStaging;
    /** @AppConfig(backend:staging/apply_after_magento) */
    protected bool $applyAfterMagento;
    protected array $disabledStaging = [];
    protected int $versionId;

    public function getVersionId(): int
    {
        return $this->versionId;
    }

    protected function defineVersionId(): void
    {
        if ($this->applyAfterMagento) {
            $this->versionId = 1;
            if ($data = $this->fetchFlag('staging')) {
                $this->versionId = (int)json_decode($data, true)['current_version'];
            }
        } else {
            $this->versionId = time();
        }
    }

    protected function fetchFlag(string $flag): ?string
    {
        return $this->db->fetchOneCol($this->getFlagSelect($flag));
    }

    protected function getFlagSelect(string $flag): Select
    {
        return $this->db->select()
            ->from('flag')
            ->columns(['flag_data'])
            ->where(['flag_code = ?' => $flag]);
    }

    public function getPreviousVersionId(): int
    {
        $version = 1;
        if ($data = $this->fetchFlag($this::PREVIOUS_VERSION_FLAG)) {
            $version = (int)$data;
        }

        return $version;
    }

    public function setPreviousVersionId(int $versionId): void
    {
        if ($this->fetchFlag($this::PREVIOUS_VERSION_FLAG)) {
            $this->db->sql(
                $this->db->update()
                    ->table('flag')
                    ->where(['flag_code = ?' => $this::PREVIOUS_VERSION_FLAG])
                    ->set(['flag_data' => $versionId])
            );
        } else {
            $this->db->sql(
                $this->db->insert()
                    ->into('flag')
                    ->values([
                        'flag_code' => $this::PREVIOUS_VERSION_FLAG,
                        'flag_data' => $versionId,
                    ])
            );
        }
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

    public function getChangedEntityIds(string $table, int $fromVersion, int $toVersion): array
    {
        return $this->db->fetchCol($this->getChangedEntityIdsSelect($table, $fromVersion, $toVersion));
    }

    public function getChangedEntityIdsSelect(string $table, int $fromVersion, int $toVersion): Select
    {
        $select = $this->db->select()
            ->from(['e' => $table])
            ->where([
                'created_in > ?' => $fromVersion,
                'created_in <= ?' => $toVersion,
            ]);

        return $this->disableStaging($select);
    }

    public function disableStaging(Select $sql): Select
    {
        $this->disabledStaging[spl_object_hash($sql)] = 1;

        return $sql;
    }

    public function isStagingEnabledForQuery(AbstractPreparableSql $sql): bool
    {
        return !isset($this->disabledStaging[spl_object_hash($sql)]);
    }

    public function cleanDisabledStaging(AbstractPreparableSql $sql): AbstractPreparableSql
    {
        unset($this->disabledStaging[spl_object_hash($sql)]);

        return $sql;
    }
}
