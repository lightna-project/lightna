<?php

declare(strict_types=1);

namespace Lightna\Magento\Index\Product;

use Lightna\Engine\App\Database;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\Data\Context;
use Lightna\Magento\App\Query\Product\Eav;
use Lightna\Magento\App\Query\Product\Gallery;
use Lightna\Magento\App\Query\Store;
use Lightna\Magento\Index\Product as ProductIndex;

class BatchDataProvider extends ObjectA
{
    protected Database $db;
    protected ProductIndex $productIndex;
    protected Context $context;
    protected Store $store;
    protected Eav $eav;
    protected Gallery $gallery;

    /** Batch variables */
    protected array $parentIds;
    protected array $children;
    protected array $childrenIds;
    protected array $allIds;
    protected array $attributes;
    protected array $prices;
    protected array $urls;
    protected array $options;
    protected array $variantValueIds;
    protected array $galleryItems;
    /** Result */
    protected array $data = [];

    public function getData(array $parentIds): array
    {
        $this->parentIds = $parentIds;
        $this->loadChildrenRelations();
        $this->allIds = array_unique(merge($this->parentIds, array_values($this->childrenIds)));

        $this->loadAttributes();
        $this->loadPrices();
        $this->loadUrls();
        $this->loadOptions();
        $this->loadVariantValueIds();
        $this->loadGallery();

        $batchSelect = $this->productIndex->getBatchSelect($this->allIds);
        foreach ($this->db->fetch($batchSelect, 'entity_id') as $id => $product) {
            if (
                // Skip products without indexed price
                !isset($this->prices[$id])
                // Skip configurable without children
                || ($product['type_id'] === 'configurable' && !isset($this->children[$id]))
            ) {
                continue;
            }

            $this->applyAttributes($product, $id);
            $this->applyPrice($product, $id);
            $this->applyStock($product, $id);
            $this->applyUrl($product, $id);
            $this->applyOptions($product, $id);
            $this->applyGallery($product, $id);

            unset($product['children']);
            $this->data[$id] = $product;
        }

        // Clean children from result (considering they are not visible individually)
        foreach ($this->data as $id => $product) {
            if (isset($this->childrenIds[$id])) {
                unset($this->data[$id]);
            }
        }

        return $this->data;
    }

    protected function loadChildrenRelations(): void
    {
        $this->children = [];
        $this->childrenIds = [];

        $select = $this->db->select('catalog_product_relation');
        $select->where->in('parent_id', $this->parentIds);

        foreach ($this->db->fetch($select) as $row) {
            $this->children[$row['parent_id']][$row['child_id']] = [];
            $this->childrenIds[$row['child_id']] = $row['child_id'];
        }
    }

    protected function loadAttributes(): void
    {
        $this->attributes = $this->eav->getAttributeValues($this->allIds, []);
    }

    protected function loadPrices(): void
    {
        $websiteId = $this->store->get($this->context->scope)['website_id'];

        $select = $this->db
            ->select('catalog_product_index_price')
            ->where(['website_id = ?' => $websiteId])
            ->order(['entity_id', 'customer_group_id']);

        $select->where->in('entity_id', $this->allIds);

        foreach ($this->db->fetch($select) as $row) {
            $isPriceEqualToDefault = (int)$row['customer_group_id'] !== 0
                && $row['final_price'] === $this->prices[$row['entity_id']][0];

            if ($isPriceEqualToDefault) {
                continue;
            }
            $this->prices[$row['entity_id']][$row['customer_group_id']] = $row['final_price'];
        }
    }

    protected function loadUrls(): void
    {
        $select = $this->db
            ->select(['u' => 'url_rewrite'])
            ->columns(['entity_id', 'request_path'])
            ->where([
                'u.store_id = ?' => $this->context->scope,
                'u.entity_type = ?' => 'product',
                'u.redirect_type = ?' => 0,
            ]);
        $select->where->in('u.entity_id', $this->allIds);

        $this->urls = $this->db->fetchCol($select, 'request_path', 'entity_id');
    }

    protected function loadOptions(): void
    {
        $select = $this->db
            ->select(['o' => 'catalog_product_super_attribute'])
            ->columns(['product_id', 'attribute_id'])
            ->join(
                ['a' => 'eav_attribute'],
                'o.attribute_id = a.attribute_id',
                ['code' => 'attribute_code', 'label' => 'frontend_label']
            )
            ->order(['o.product_id', 'o.position']);

        $select->where->in('o.product_id', $this->allIds);

        foreach ($this->db->fetch($select) as $row) {
            $this->options[$row['product_id']][] = [
                'id' => $row['attribute_id'],
                'code' => $row['code'],
                'label' => $row['label'],
            ];
        }
    }

    protected function applyAttributes(array &$product, string|int $id): void
    {
        $product = merge(
            $product,
            $this->attributes[$id],
        );
    }

    protected function applyPrice(array &$product, string|int $id): void
    {
        if (!isset($this->children[$id])) {
            $product['price'] = $this->getSimplePriceData($product, $id);
        } else {
            $this->applyConfigurablePriceData($product, $id);
        }
    }

    protected function getSimplePriceData(array $product, string|int $id): array
    {
        $regular = (float)$product['price'];
        $finalPrices = [];
        $discountPercents = [];
        $discounts = [];
        foreach ($this->prices[$id] as $groupId => $final) {
            $finalPrices[$groupId] = round((float)$final, 2);
            $discounts[$groupId] = round($regular - $final, 2);
            $discountPercents[$groupId] =
                $regular ? round(($regular - $final) / $regular * 100) : 0;
        }

        return [
            'regular' => round((float)$regular, 2),
            'final_prices' => $finalPrices,
            'discounts' => $discounts,
            'discount_percents' => $discountPercents,
        ];
    }

    protected function applyConfigurablePriceData(array &$product, string|int $id): void
    {
        $minPrice = null;
        foreach ($this->children[$id] as $childId => $null) {
            // Simple products already handled because of batch sorting thus price exists
            $product['children'][$childId]['price'] = $this->data[$childId]['price'];

            if ($minPrice === null || $this->data[$childId]['price']['final_prices'] < $minPrice) {
                $minPrice = $this->data[$childId]['price'];
            }
        }

        $product['price'] = $minPrice;
    }

    protected function applyStock(array &$product, string|int $id): void
    {
        if (isset($this->children[$id])) {
            $qty = 0;
            $inStock = 0;
            foreach ($this->children[$id] as $childId => $null) {
                $childStock = $this->data[$childId]['stock'];
                $product['children'][$childId]['stock'] = $childStock;

                $qty += $childStock['qty'];
                $inStock = $inStock || $childStock['is_in_stock'];
            }

            $product['qty'] = $qty;
            $product['is_in_stock'] = $inStock;
        }

        // Remove .0000
        $product['qty'] += 0;
        settype($product['is_in_stock'], 'bool');
        settype($product['backorders'], 'bool');

        $this->moveInside(
            'stock',
            ['qty', 'is_in_stock', 'backorders'],
            $product
        );
    }

    protected function applyUrl(array &$product, string|int $id): void
    {
        if (!isset($this->urls[$id])) {
            return;
        }

        $url = $this->urls[$id];
        $isRelative = !preg_match('~^(https?://|/)~', $url);
        $product['url'] = $isRelative ? '/' . $url : $url;
    }

    protected function applyOptions(array &$product, string|int $id): void
    {
        if ($product['type_id'] === 'configurable') {
            $product['options']['attributes'] = $this->options[$id];
        } else {
            $product['options']['attributes'] = [];
            return;
        }

        foreach ($this->children[$id] as $childId => $null) {
            $variant = ['product_id' => $childId, 'values' => []];
            foreach ($this->options[$id] as $option) {
                $variant['values'][] = [
                    'code' => $option['code'],
                    'label' => $this->data[$childId][$option['code']],
                    'id' => $this->variantValueIds[$childId][$option['code']],
                ];
            }
            $product['options']['variants'][] = $variant;
        }
    }

    protected function applyGallery(array &$product, string|int $id): void
    {
        $gallery = [];
        $images = $this->galleryItems[$id] ?? ['/coming-soon.jpg'];
        foreach ($images as $image) {
            $types = $this->gallery->getCompressedTypes($image);
            $types['max'] = ltrim($image, '/');
            $gallery[] = $types;
        }
        $product['gallery'] = $gallery;
    }

    protected function loadVariantValueIds(): void
    {
        $entityIds = [];
        $attributeCodes = [];
        foreach ($this->attributes as $productId => $null1) {
            foreach ($this->children[$productId] ?? [] as $childId => $null2) {
                if (!isset($this->options[$productId])) {
                    // It's not configurable but bundle or grouped, skip it
                    continue;
                }
                $entityIds[$childId] = $childId;
                foreach ($this->options[$productId] as $option) {
                    $attributeCodes[$option['code']] = $option['code'];
                }
            }
        }

        foreach ($this->eav->getAttributeValuesRaw($entityIds, $attributeCodes) as $attrs) {
            foreach ($attrs as $code => $attr) {
                $this->variantValueIds[$attr['entity_id']][$code] = $attr['value'];
            }
        }
    }

    protected function loadGallery(): void
    {
        $this->galleryItems = $this->gallery->getItems($this->allIds);
    }

    protected function moveInside(string $toKey, array $fromKeys, array &$subject): void
    {
        foreach ($fromKeys as $oldName => $newName) {
            $oldName = is_string($oldName) ? $oldName : $newName;
            $subject[$toKey][$newName] = $subject[$oldName];
            unset($subject[$oldName]);
        }
    }
}
