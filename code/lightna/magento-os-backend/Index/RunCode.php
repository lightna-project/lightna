<?php

declare(strict_types=1);

namespace Lightna\Magento\Index;

use Lightna\Engine\App\Index\IndexAbstract;
use Lightna\Magento\App\Entity\RunCode as RunCodeEntity;
use Lightna\Magento\App\Query\Store;
use Lightna\Magento\App\Query\Website;

class RunCode extends IndexAbstract
{
    protected RunCodeEntity $entity;
    protected Store $store;
    protected Website $website;

    public function getDataBatch(array $ids): array
    {
        return [1 => $this->getRunCodes()];
    }

    protected function getRunCodes(): array
    {
        $stores = $this->store->getList();
        $websites = $this->website->getList();
        $runCodes = [];
        foreach ($stores as $store) {
            if (!$store['is_active']) {
                continue;
            }
            $runCodes['store'][$store['code']] = $store['store_id'];
            $website = $websites[$store['website_id']];
            $runCodes['website'][$website['code']] ??= $store['store_id'];
        }

        return $runCodes;
    }

    public function scan(int|string $lastId = null): array
    {
        // Scan is done in a single batch for id=1
        return $lastId ? [] : [1];
    }

    public function gcCheck(array $ids): array
    {
        return array_diff($ids, [1]);
    }
}
