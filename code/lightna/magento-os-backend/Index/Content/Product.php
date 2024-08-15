<?php

declare(strict_types=1);

namespace Lightna\Magento\Index\Content;

use Lightna\Magento\App\Entity\Content\Product as ProductContentEntity;
use Lightna\Magento\Index\Content\Provider\Product as ProductContentProvider;
use Lightna\Magento\App\Index\ScopeIndexAbstract;

class Product extends ScopeIndexAbstract
{
    protected ProductContentEntity $entity;
    protected ProductContentProvider $scopeDataProvider;
}
