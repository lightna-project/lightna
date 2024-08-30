<?php

declare(strict_types=1);

namespace Lightna\Magento\Data\Content;

use Lightna\Engine\App\Context;
use Lightna\Engine\Data\DataA;
use Lightna\Magento\Data\Content\Page\Menu\Item as MenuItem;

/**
 * @property-read string footerLinksHtml
 * @property-read string uspHtml
 */
class Page extends DataA
{
    public MenuItem $menu;

    /** @AppConfig(entity/content_page/entity) */
    protected string $contentPageEntity;
    protected Context $context;

    protected function init($data = []): void
    {
        parent::init($this->getEntityData());
    }

    protected function getEntityData(): array
    {
        return getobj($this->contentPageEntity)->get($this->context->scope);
    }
}
