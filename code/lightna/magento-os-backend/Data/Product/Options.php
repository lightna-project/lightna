<?php

declare(strict_types=1);

namespace Lightna\Magento\Data\Product;

use Lightna\Engine\Data\DataA;
use Lightna\Magento\Data\Product\Options\Attribute;
use Lightna\Magento\Data\Product\Options\Option;

/**
 * @property Attribute[] $attributes
 */
class Options extends DataA
{
    public array $attributes;
    public array $variants;

    /**
     * @return Option[]
     */
    public function getAttributeOptions(string $code): array
    {
        $options = [];
        foreach ($this->variants as $variant) {
            foreach ($variant->values as $value) {
                if ($value->code === $code) {
                    $options[$value->id] = newobj(
                        Option::class,
                        [
                            'id' => $value->id,
                            'label' => $value->label,
                            'attributeCode' => $code,
                        ]
                    );
                }
            }
        }

        return $options;
    }
}
