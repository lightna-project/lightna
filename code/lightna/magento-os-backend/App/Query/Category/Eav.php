<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Query\Category;

use Lightna\Magento\App\Query\EavAbstract;

class Eav extends EavAbstract
{
    const ENTITY_TYPE = 3;
    const ENTITY_TABLE = 'catalog_category_entity';
}
