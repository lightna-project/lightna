<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Index\Content;

use Lightna\Magento\Backend\App\Entity\Content\Page as PageContentEntity;
use Lightna\Magento\Backend\App\Index\ScopeIndexAbstract;
use Lightna\Magento\Backend\Index\Provider\Content\Page as PageContentProvider;

class Page extends ScopeIndexAbstract
{
    protected PageContentEntity $entity;
    protected PageContentProvider $scopeDataProvider;
}
