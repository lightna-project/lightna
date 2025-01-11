<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Query\Index;

use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Select;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;
use Lightna\Magento\App\Query\Customer\Group;
use Lightna\Magento\App\Query\Website;

class Price extends ObjectA
{
    protected Database $db;
    protected Website $website;
    protected Group $group;
    protected int $rowsBatchSize;
    protected int $syncBatchSize;

    /** @noinspection PhpUnused */
    protected function defineRowsBatchSize(): void
    {
        $this->rowsBatchSize = 40000;
    }

    /** @noinspection PhpUnused */
    protected function defineSyncBatchSize(): void
    {
        $this->syncBatchSize = (int)(
            $this->rowsBatchSize
            / count($this->website->getList())
            / count($this->group->getList())
        );
    }

    public function syncReplica(): void
    {
        $from = 0;
        while ($to = $this->getNextTo($from)) {
            $this->updateReplica($from, $to);
            $this->cleanReplica($from, $to);
            $from = $to + 1;
        }

        $this->cleanReplicaRemainder($from);
    }

    protected function getNextTo(int $from): ?int
    {
        return $this->db->query(
            $this->getNextToQuery(),
            [$from, $this->syncBatchSize],
        )->next()['to'];
    }

    protected function getNextToQuery(): string
    {
        return
            'select max(entity_id) as "to" from (' .
            '    select distinct entity_id from catalog_product_index_price' .
            '    where entity_id >= ?' .
            '    order by entity_id' .
            '    limit ?' .
            ') as t';
    }

    protected function updateReplica(int $from, int $to): void
    {
        $this->db->query($this->getUpdateReplicaQuery($from, $to));
    }

    public function getUpdateReplicaQuery(int $from, int $to): string
    {
        $insert = $this->db->insert()
            ->into('catalog_product_index_price_replica')
            ->values($this->getUpdateReplicaSelect($from, $to));

        return $this->db->buildSqlString($insert) .
            ' ON DUPLICATE KEY UPDATE' .
            ' tax_class_id = VALUES(tax_class_id),' .
            ' price = VALUES(price),' .
            ' final_price = VALUES(final_price),' .
            ' min_price = VALUES(min_price),' .
            ' max_price = VALUES(max_price),' .
            ' tier_price = VALUES(tier_price)';
    }

    protected function getUpdateReplicaSelect(int $from, int $to): Select
    {
        return $this->db->select()
            ->from(['p' => 'catalog_product_index_price'])
            ->where([
                'p.entity_id >= ?' => $from,
                'p.entity_id <= ?' => $to,
            ]);
    }

    protected function cleanReplica(int $from, int $to): void
    {
        $this->db->query($this->getCleanReplicaQuery(), [$from, $to]);
    }

    protected function getCleanReplicaQuery(): string
    {
        return
            'DELETE replica ' .
            'FROM catalog_product_index_price_replica replica ' .
            'LEFT JOIN catalog_product_index_price price USING (entity_id) ' .
            'WHERE replica.entity_id >= ? AND replica.entity_id <= ? AND price.entity_id IS NULL';
    }

    protected function cleanReplicaRemainder(int $from): void
    {
        $this->db->discreteWrite($this->getCleanReplicaRemainderDelete($from));
    }

    protected function getCleanReplicaRemainderDelete(int $from): Delete
    {
        return $this->db
            ->delete('catalog_product_index_price_replica')
            ->where(['entity_id >= ?' => $from]);
    }
}
