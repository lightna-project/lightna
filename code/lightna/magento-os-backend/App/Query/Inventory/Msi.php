<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Query\Inventory;

use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;
use Lightna\Magento\App\Query\Store;

class Msi extends ObjectA
{
    protected Database $db;
    protected Store $store;
    protected array $websiteSources;
    protected array $websiteStock;

    protected function defineWebsiteSources(): void
    {
        $this->websiteSources = [];
        foreach ($this->db->fetch($this->getWebsiteSourcesSelect()) as $row) {
            foreach ([$row['website_code'], $row['website_id']] as $id) {
                $this->websiteSources[$id][$row['source_code']] = $row;
                $this->websiteStock[$id] = $row['stock_id'];
            }
        }
    }

    /** @noinspection PhpUnused */
    protected function defineWebsiteStock(): void
    {
        $this->defineWebsiteSources();
    }

    public function getWebsiteSources(int|string $websiteIdOrCode): array
    {
        return $this->websiteSources[$websiteIdOrCode] ?? [];
    }

    protected function getWebsiteSourcesSelect(): Select
    {
        return $this->db->select()
            ->from(['s' => 'inventory_source'])
            ->columns(['name'])
            ->join(
                ['l' => 'inventory_source_stock_link'],
                'l.source_code = s.source_code',
                ['source_code', 'stock_id'],
            )
            ->join(
                ['c' => 'inventory_stock_sales_channel'],
                'c.stock_id = l.stock_id',
                ['website_code' => 'code'],
            )
            ->join(
                ['w' => 'store_website'],
                'w.code = c.code',
                ['website_id'],
            )
            ->where('s.enabled = 1');
    }

    public function getBatch(array $productIds): array
    {
        $websiteId = $this->store->getWebsiteId();
        $sourceCodes = array_keys($this->getWebsiteSources($websiteId));

        $inventory = $this->getInventoryBatch($sourceCodes, $productIds);
        $this->subtractReservation($inventory, $this->websiteStock[$websiteId], $productIds);

        return $inventory;
    }

    protected function getInventoryBatch(array $sourceCodes, array $productIds): array
    {
        $items = $this->db->fetch($this->getInventoryBatchSelect($sourceCodes, $productIds));
        $default = ['qty' => 0, 'status' => false, 'backorders' => false];
        $inventory = [];

        foreach ($items as $row) {
            $ref = &$inventory[$row['product_id']];
            $ref ??= $default;
            $ref['qty'] += (int)$row['quantity'];
            $ref['status'] = $ref['status'] || $row['status'];
            $ref['backorders'] = $ref['backorders'] || $row['backorders'];
        }

        return $inventory;
    }

    protected function getInventoryBatchSelect(array $sourceCodes, array $productIds): Select
    {
        $select = $this->db->select()
            ->from(['i' => 'inventory_source_item'])
            ->join(
                ['p' => 'catalog_product_entity'],
                'p.sku = i.sku',
                ['product_id' => 'entity_id'])
            ->join(
                ['ssi' => 'cataloginventory_stock_item'],
                'ssi.product_id = p.entity_id',
                ['backorders'],
            );

        $select->where->in('i.source_code', $sourceCodes);
        $select->where->in('p.entity_id', $productIds);

        return $select;
    }

    protected function subtractReservation(array &$inventory, int $stockId, array $productIds): void
    {
        $reservations = $this->db->fetch($this->getReservationBatchSelect($stockId, $productIds), 'product_id');
        foreach ($reservations as $productId => $reservation) {
            if (isset($inventory[$productId])) {
                $inventory[$productId]['qty'] += (int)$reservation['reserved'];
            }
        }
    }

    protected function getReservationBatchSelect(int $stockId, array $productIds): Select
    {
        $select = $this->db->select()
            ->from(['r' => 'inventory_reservation'])
            ->columns(['reserved' => new Expression('SUM(quantity)')])
            ->join(
                ['p' => 'catalog_product_entity'],
                'p.sku = r.sku',
                ['product_id' => 'entity_id'],
            )
            ->where(['stock_id = ?' => $stockId])
            ->group(['product_id']);

        $select->where->in('p.entity_id', $productIds);

        return $select;
    }
}
