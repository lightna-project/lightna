<?php

declare(strict_types=1);

namespace Lightna\Magento\Index\Content\Provider;

use Lightna\Engine\App\ObjectA;
use Lightna\Magento\App\Index\DataProvider\Cms\Block as CmsBlockProvider;
use Lightna\Magento\App\Query\Categories;

class Page extends ObjectA
{
    protected CmsBlockProvider $cmsBlockProvider;
    /** @AppConfig(magento/page/blocks) */
    protected array $blocks;
    protected Categories $categories;

    public function getData(): array
    {
        return merge(
            $this->cmsBlockProvider->getData($this->blocks),
            ['menu' => $this->getCategoriesTree()],
        );
    }

    protected function getCategoriesTree(): array
    {
        $tree = $this->buildCategoriesTree(
            $this->categories->getList(),
            $this->categories->getRootId(),
        );

        return array_camel([
            'entity_id' => 0,
            'level' => 0,
            'parent_id' => 0,
            'children' => $tree,
        ]);
    }

    protected function buildCategoriesTree(array $items, int $parentId, $level = 1): array
    {
        $tree = [];
        foreach ($items as $item) {
            if ($item['parent_id'] === $parentId) {
                $treeItem = $item;
                $treeItem['level'] = $level;
                $treeItem['children'] = $this->buildCategoriesTree(
                    $items,
                    $item['entity_id'],
                    $level + 1,
                );

                $tree[$item['entity_id']] = $treeItem;
            }
        }

        return $tree;
    }
}
