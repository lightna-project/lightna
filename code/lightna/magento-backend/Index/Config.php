<?php

declare(strict_types=1);

namespace Lightna\Magento\Index;

use Lightna\Magento\App\Entity\Config as ConfigEntity;
use Lightna\Magento\App\Index\ScopeIndexAbstract;
use Lightna\Magento\Index\Provider\Config as ConfigProvider;

class Config extends ScopeIndexAbstract
{
    protected ConfigEntity $entity;
    protected ConfigProvider $scopeDataProvider;
}
