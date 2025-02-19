<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Index\Provider\Content;

use Lightna\Engine\App\ObjectA;
use Lightna\Magento\Backend\App\Index\DataProvider\Cms\Block as CmsBlockProvider;
use Lightna\Magento\Backend\App\Query\Category;

class Page extends ObjectA
{
    protected CmsBlockProvider $cmsBlockProvider;
    /** @AppConfig(backend:magento/page/blocks) */
    protected array $blocks;
    protected Category $category;
    protected array $treeItemFields = [
        'entity_id',
        'name',
        'url',
    ];

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
            $this->category->getNavigation(),
            $this->category->getRootId(),
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
                $treeItem = $this->categoryToTreeItem($item);
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

    protected function categoryToTreeItem(array $item): array
    {
        return array_intersect_key(
            $item,
            array_flip($this->treeItemFields),
        );
    }
}
