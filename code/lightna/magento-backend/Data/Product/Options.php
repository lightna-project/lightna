<?php

declare(strict_types=1);

namespace Lightna\Magento\Data\Product;

use Lightna\Engine\Data\DataA;
use Lightna\Engine\Data\Request;
use Lightna\Magento\Data\Product\Options\Attribute;
use Lightna\Magento\Data\Product\Options\Option;

/**
 * @property Attribute[] $attributes
 */
class Options extends DataA
{
    public array $attributes;
    public array $variants;

    protected Request $request;
    protected array $attributeOptions;
    protected array $selected;

    /** @noinspection PhpUnused */
    protected function defineSelected(): void
    {
        $this->selected = [];
        foreach ($this->attributes as $attribute) {
            $this->selected[$attribute->code] = $this->request->param->super_attribute[$attribute->id] ?? false;
        }
    }

    /** @noinspection PhpUnused */
    protected function defineAttributeOptions(): void
    {
        $this->attributeOptions = [];
        foreach ($this->variants as $variant) {
            foreach ($variant->values as $value) {
                $this->attributeOptions[$value->code][$value->id] = [
                    'id' => $value->id,
                    'label' => $value->label,
                    'attributeCode' => $value->code,
                    'selected' => $this->selected[$value->code] == $value->id,
                ];
            }
        }

        $this->applyAttributeOptionsAvailability();
    }

    protected function applyAttributeOptionsAvailability(): void
    {
        $avl = [];
        foreach ($this->variants as $variant) {
            $avlKey = [];
            foreach ($variant->values as $value) {
                $avlKey[] = $value->id;
            }
            $avl[implode(',', $avlKey)] = true;
        }

        $filtersCount = count($this->attributeOptions);
        foreach ($this->attributeOptions as &$options) {
            $filter = $this->selected;
            foreach ($options as &$option) {
                $filter[$option['attributeCode']] = $option['id'];
                $filter = array_filter($filter);
                $option['available'] = count($filter) !== $filtersCount
                    || isset($avl[implode(',', $filter)]);
            }
        }
    }

    /**
     * @return Option[]
     */
    public function getAttributeOptions(string $code): array
    {
        $options = [];
        foreach ($this->attributeOptions[$code] as $id => $option) {
            $options[$id] = newobj(Option::class, $option);
        }

        return $options;
    }

    public function getCurrentAttributeValue(string $code): string
    {
        if (!$selectedValue = $this->selected[$code]) {
            return '';
        }
        $option = $this->attributeOptions[$code][$selectedValue];

        return $option['available'] ? $selectedValue : '';
    }
}
