<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Query;

use Lightna\Engine\App\Context;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;
use Lightna\Magento\App\Query\Inventory\Msi;
use Lightna\Magento\App\Query\Inventory\Ssi;

class Inventory extends ObjectA
{
    protected Database $db;
    protected Context $context;
    protected Msi $msi;
    protected Ssi $ssi;
    protected bool $msiExists;

    /** @noinspection PhpUnused */
    protected function defineMsiExists(): void
    {
        $this->msiExists = $this->db->structure->tableExists('inventory_source');
    }

    public function getBatch(array $productIds): array
    {
        if ($this->msiExists) {
            return $this->msi->getBatch($productIds);
        } else {
            return $this->ssi->getBatch($productIds);
        }
    }
}
