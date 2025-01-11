<?php

declare(strict_types=1);

namespace Lightna\Magento\Index\Content;

use Lightna\Magento\App\Entity\Content\Page as PageContentEntity;
use Lightna\Magento\App\Index\ScopeIndexAbstract;
use Lightna\Magento\Index\Provider\Content\Page as PageContentProvider;

class Page extends ScopeIndexAbstract
{
    protected PageContentEntity $entity;
    protected PageContentProvider $scopeDataProvider;
}
