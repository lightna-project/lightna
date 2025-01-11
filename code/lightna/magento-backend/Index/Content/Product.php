<?php

declare(strict_types=1);

namespace Lightna\Magento\Index\Content;

use Lightna\Magento\App\Entity\Content\Product as ProductContentEntity;
use Lightna\Magento\App\Index\ScopeIndexAbstract;
use Lightna\Magento\Index\Provider\Content\Product as ProductContentProvider;

class Product extends ScopeIndexAbstract
{
    protected ProductContentEntity $entity;
    protected ProductContentProvider $scopeDataProvider;
}
