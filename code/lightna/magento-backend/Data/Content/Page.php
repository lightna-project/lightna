<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data\Content;

use Lightna\Engine\Data\DataA;
use Lightna\Magento\Backend\Data\Content\Page\Category\Item as CategoryItem;
use Lightna\Magento\Backend\Data\Content\Page\Menu\Item as MenuItem;

class Page extends DataA
{
    public MenuItem $menu;
    public string $footerLinksHtml = '';
    public string $uspHtml = '';
    /** @var CategoryItem[] */
    public array $category = [];

    /** @AppConfig(entity/content_page/entity) */
    protected string $contentPageEntity;

    protected function init(array $data = []): void
    {
        parent::init($this->getEntityData());
    }

    protected function getEntityData(): array
    {
        return getobj($this->contentPageEntity)->get(1);
    }
}
