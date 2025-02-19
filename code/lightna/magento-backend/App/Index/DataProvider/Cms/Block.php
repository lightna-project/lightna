<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Index\DataProvider\Cms;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\Context;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;

class Block extends ObjectA
{
    protected Database $db;
    protected Context $context;
    protected array $blockMap;

    public function getData(array $blockMap): array
    {
        $this->blockMap = $blockMap;

        $fetch = $this->fetchBlocks();
        $result = [];
        foreach ($this->blockMap as $name => $id) {
            if (isset($fetch[$id])) {
                $result[$name] = $fetch[$id];
            }
        }

        return $result;
    }

    protected function fetchBlocks(): array
    {
        $fetch = [];
        foreach ($this->db->fetch($this->getBlocksSelect()) as $row) {
            // Override default with store specific
            $fetch[$row['identifier']] = $row['content'];
        }

        return $fetch;
    }

    protected function getBlocksSelect(): Select
    {
        $select = $this->db
            ->select(['b' => 'cms_block'])
            ->join(
                ['bs' => 'cms_block_store'],
                'b.block_id = bs.block_id',
                ['store_id']
            )
            ->where('is_active = 1')
            ->order('bs.store_id');

        $select->where
            ->in('identifier', $this->blockMap)
            ->in('bs.store_id', [0, $this->context->scope]);

        return $select;
    }
}
