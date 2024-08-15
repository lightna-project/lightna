<?php

declare(strict_types=1);

namespace Lightna\Magento\Index\Content;

use Lightna\Magento\App\Entity\Content\Category as CategoryContentEntity;
use Lightna\Magento\Index\Content\Provider\Category as CategoryContentProvider;
use Lightna\Magento\App\Index\ScopeIndexAbstract;

class Category extends ScopeIndexAbstract
{
    protected CategoryContentEntity $entity;
    protected CategoryContentProvider $scopeDataProvider;
}
