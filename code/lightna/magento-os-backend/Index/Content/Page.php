<?php

declare(strict_types=1);

namespace Lightna\Magento\Index\Content;

use Lightna\Magento\App\Entity\Content\Page as PageContentEntity;
use Lightna\Magento\Index\Content\Provider\Page as PageContentProvider;
use Lightna\Magento\App\Index\ScopeIndexAbstract;

class Page extends ScopeIndexAbstract
{
    protected PageContentEntity $entity;
    protected PageContentProvider $scopeDataProvider;
}
