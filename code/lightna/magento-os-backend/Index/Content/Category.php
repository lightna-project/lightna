<?php

declare(strict_types=1);

namespace Lightna\Magento\Index\Content;

use Lightna\Magento\App\Entity\Content\Category as CategoryContentEntity;
use Lightna\Magento\App\Index\ScopeIndexAbstract;
use Lightna\Magento\Index\Provider\Content\Category as CategoryContentProvider;

class Category extends ScopeIndexAbstract
{
    protected CategoryContentEntity $entity;
    protected CategoryContentProvider $scopeDataProvider;
}
