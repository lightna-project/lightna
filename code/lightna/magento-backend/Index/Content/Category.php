<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Index\Content;

use Lightna\Magento\Backend\App\Entity\Content\Category as CategoryContentEntity;
use Lightna\Magento\Backend\App\Index\ScopeIndexAbstract;
use Lightna\Magento\Backend\Index\Provider\Content\Category as CategoryContentProvider;

class Category extends ScopeIndexAbstract
{
    protected CategoryContentEntity $entity;
    protected CategoryContentProvider $scopeDataProvider;
}
