<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Query\Category;

use Lightna\Magento\App\Query\EavAbstract;

class Eav extends EavAbstract
{
    public const ENTITY_TYPE = 3;
    public const ENTITY_TABLE = 'catalog_category_entity';
}
