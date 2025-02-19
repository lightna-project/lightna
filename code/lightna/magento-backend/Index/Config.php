<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Index;

use Lightna\Magento\Backend\App\Entity\Config as ConfigEntity;
use Lightna\Magento\Backend\App\Index\ScopeIndexAbstract;
use Lightna\Magento\Backend\Index\Provider\Config as ConfigProvider;

class Config extends ScopeIndexAbstract
{
    protected ConfigEntity $entity;
    protected ConfigProvider $scopeDataProvider;
}
