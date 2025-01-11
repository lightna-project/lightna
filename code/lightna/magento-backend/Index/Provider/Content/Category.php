<?php

declare(strict_types=1);

namespace Lightna\Magento\Index\Provider\Content;

use Lightna\Engine\App\ObjectA;
use Lightna\Magento\App\Query\Product\Eav;

class Category extends ObjectA
{
    protected Eav $eav;

    public function getData(): array
    {
        return [
            'filterableAttributes' => $this->getFilterableAttributes()
        ];
    }

    protected function getFilterableAttributes(): array
    {
        $attributes = $this->eav->getFilterableAttributes();

        $options = $this->eav->getOptions();
        foreach ($attributes as &$attribute) {
            $attribute['options'] = $options[$attribute['id']] ?? [];
        }

        return $attributes;
    }
}
