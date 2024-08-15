<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Index;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\Database;
use Lightna\Engine\App\Index\IndexAbstract;
use Lightna\Engine\Data\Context;

abstract class ScopeIndexAbstract extends IndexAbstract
{
    protected Database $db;
    protected Context $context;

    public function getBatchData(array $ids): array
    {
        $data = [];
        $store = $this->db->fetchOne($this->getStoreSelect());
        if (!$this->isStoreAvailable($store)) {
            return $data;
        }

        if ($scopeData = $this->getScopeData()) {
            $data[$this->context->scope] = $scopeData;
        }

        return $data;
    }

    protected function isStoreAvailable(array $store): bool
    {
        return (bool)$store['is_active'];
    }

    protected function getScopeData(): ?array
    {
        return $this->scopeDataProvider->getData();
    }

    public function getStoreSelect(): Select
    {
        return $this->db->select('store')
            ->where(['store_id = ?' => $this->context->scope]);
    }

    public function scan(string|int $lastId = null): array
    {
        if ($lastId) {
            // Scan is done in a single batch
            return [];
        }

        // IDs not used in ScopeIndexAbstract, we just need single value here
        return [1];
    }
}
