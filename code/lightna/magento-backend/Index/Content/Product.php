<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Index\Content;

use Lightna\Magento\Backend\App\Entity\Content\Product as ProductContentEntity;
use Lightna\Magento\Backend\App\Index\ScopeIndexAbstract;
use Lightna\Magento\Backend\Index\Provider\Content\Product as ProductContentProvider;

class Product extends ScopeIndexAbstract
{
    protected ProductContentEntity $entity;
    protected ProductContentProvider $scopeDataProvider;
}
