<?php

declare(strict_types=1);

namespace Lightna\Magento\Staging\App\Query;

use Laminas\Db\Sql\AbstractPreparableSql;
use Laminas\Db\Sql\Select;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;
use Lightna\Magento\App\Query\Flag;
use Lightna\Magento\Staging\App\Staging as AppStaging;

class Staging extends ObjectA
{
    public const PREVIOUS_VERSION_FLAG = 'lightna_staging_previous_version';
    protected Database $db;
    protected Flag $flag;
    protected AppStaging $appStaging;
    /** @AppConfig(backend:staging/apply_after_magento) */
    protected bool $applyAfterMagento;
    protected array $disabledStaging = [];
    protected int $versionId;

    public function getVersionId(): int
    {
        return $this->versionId;
    }

    /** @noinspection PhpUnused */
    protected function defineVersionId(): void
    {
        if ($this->applyAfterMagento) {
            $this->versionId = 1;
            if ($flag = $this->flag->get('staging')) {
                $this->versionId = (int)$flag['current_version'];
            }
        } else {
            $this->versionId = time();
        }
    }

    public function getPreviousVersionId(): int
    {
        return $this->flag->get($this::PREVIOUS_VERSION_FLAG) ?? 1;
    }

    public function setPreviousVersionId(int $versionId): void
    {
        $this->flag->set($this::PREVIOUS_VERSION_FLAG, $versionId);
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
