<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Index;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\Context;
use Lightna\Engine\App\Index\IndexAbstract;
use Lightna\Engine\App\Project\Database;

abstract class ScopeIndexAbstract extends IndexAbstract
{
    protected Database $db;
    protected Context $context;

    public function getDataBatch(array $ids): array
    {
        $data = [];
        $store = $this->db->fetchOne($this->getStoreSelect());
        if (!$this->isStoreAvailable($store)) {
            return $data;
        }

        if ($scopeData = $this->getScopeData()) {
            $data[1] = $scopeData;
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

    protected function getStoreSelect(): Select
    {
        return $this->db->select('store')
            ->where(['store_id = ?' => $this->context->scope]);
    }

    public function scan(string|int $lastId = null): array
    {
        // Scan is done in a single batch for id=1
        return $lastId ? [] : [1];
    }

    public function gcCheck(array $ids): array
    {
        return array_diff($ids, [1]);
    }
}
