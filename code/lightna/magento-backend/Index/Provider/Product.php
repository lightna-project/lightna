<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Index\Provider;

use Lightna\Engine\App\ObjectA;
use Lightna\Magento\Backend\App\Query\Inventory;
use Lightna\Magento\Backend\App\Query\Product as ProductQuery;
use Lightna\Magento\Backend\App\Query\Product\Eav;
use Lightna\Magento\Backend\App\Query\Product\Gallery;
use Lightna\Magento\Backend\App\Query\Url;

class Product extends ObjectA
{
    protected ProductQuery $product;
    protected Eav $eav;
    protected Gallery $gallery;
    protected Inventory $inventoryQuery;
    protected Url $urlQuery;
    /** @AppConfig(backend:indexer/product/raw_value_attributes) */
    protected array $rawValueAttributes;

    /** Batch variables */
    protected array $ids;
    protected array $children;
    protected array $parents;
    protected array $allIds;
    protected array $attributes;
    protected array $prices;
    protected array $inventory;
    protected array $urls;
    protected array $options;
    protected array $variantValueIds;
    protected array $galleryItems;
    protected array $categories;
    protected array $relatedIds;
    /** Result */
    protected array $data = [];

    public function getData(array $ids): array
    {
        $this->ids = $this->allIds = $ids;
        $this->addRelations();
        $this->loadData();

        foreach ($this->product->getBatch($this->allIds) as $id => $product) {
            if (!$this->isProductIndexable($product)) {
                $this->unloadProduct($id);
                continue;
            }

            $this->collectProductData($product, $id);
            $this->applyProductData($product, $id);
        }

        $this->applyData();

        return $this->data;
    }

    protected function addRelations(): void
    {
        $this->loadChildrenRelations();
        $this->allIds = array_unique(merge($this->allIds, array_keys($this->parents)));
    }

    protected function loadData(): void
    {
        $this->loadAttributes();
        $this->loadPrices();
        $this->loadInventory();
        $this->loadUrls();
        $this->loadOptions();
        $this->loadVariantValueIds();
        $this->loadGallery();
        $this->loadCategories();
        $this->loadRelatedIds();
    }

    protected function isProductIndexable(array $product): bool
    {
        $na =
            // Skip products without indexed price
            !isset($this->prices[$product['entity_id']])
            // Skip configurable without children
            || ($product['type_id'] === 'configurable' && empty($this->children[$product['entity_id']]));

        return !$na;
    }

    protected function unloadProduct(int|string $id): void
    {
        if (isset($this->parents[$id])) {
            unset($this->children[$this->parents[$id]][$id]);
            unset($this->parents[$id]);
        }
    }

    protected function isProductAvailable(array $product): bool
    {
        $inventory = $product['inventory'];
        $na =
            // Clean children from result (considering they are not visible individually)
            isset($this->parents[$product['entity_id']])
            // Skip out of stock products
            || (!$inventory['status'] && !$inventory['backorders']);

        return !$na;
    }

    protected function collectProductData(array &$product, int $id): void
    {
        $this->collectAttributes($product, $id);
        $this->collectPrice($product, $id);
        $this->collectInventory($product, $id);
        $this->collectUrl($product, $id);
        $this->collectOptions($product, $id);
        $this->collectGallery($product, $id);
        $this->collectCategories($product, $id);
        $this->collectRelatedIds($product, $id);
    }

    protected function applyProductData($product, $id): void
    {
        $this->data[$id] = $product;
    }

    protected function applyData(): void
    {
        foreach ($this->data as $id => $product) {
            if (!$this->isProductAvailable($product)) {
                unset($this->data[$id]);
            }
        }
    }

    protected function loadChildrenRelations(): void
    {
        $this->children = [];
        $this->parents = [];

        foreach ($this->product->getChildrenRelations($this->ids) as $row) {
            $this->children[$row['parent_id']][$row['child_id']] = [];
            $this->parents[$row['child_id']] = $row['parent_id'];
        }
    }

    protected function loadAttributes(): void
    {
        $this->attributes = $this->eav->getAttributeValues($this->allIds, [], $this->rawValueAttributes);
    }

    protected function loadPrices(): void
    {
        foreach ($this->product->getPrices($this->allIds) as $row) {
            $isPriceEqualToDefault = (int)$row['customer_group_id'] !== 0
                && $row['final_price'] === $this->prices[$row['entity_id']][0];

            if ($isPriceEqualToDefault) {
                continue;
            }
            $this->prices[$row['entity_id']][$row['customer_group_id']] = $row['final_price'];
        }
    }

    protected function loadInventory(): void
    {
        $this->inventory = $this->inventoryQuery->getBatch($this->allIds);
    }

    protected function loadUrls(): void
    {
        $this->urls = $this->urlQuery->getEntityDirectUrlsBatch('product', $this->allIds);
    }

    protected function loadOptions(): void
    {
        foreach ($this->product->getConfigurableOptions($this->allIds) as $row) {
            $this->options[$row['product_id']][] = [
                'id' => $row['attribute_id'],
                'code' => $row['code'],
                'label' => $row['label'],
            ];
        }
    }

    protected function collectAttributes(array &$product, int $id): void
    {
        $product = merge(
            $product,
            $this->attributes[$id],
        );
    }

    protected function collectPrice(array &$product, int $id): void
    {
        if (!isset($this->children[$id])) {
            $product['price'] = $this->getSimplePriceData($product, $id);
        } else {
            $this->collectConfigurablePriceData($product, $id);
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

    protected function collectConfigurablePriceData(array &$product, string|int $id): void
    {
        $minPrice = null;
        foreach ($this->children[$id] as $childId => $null) {
            if ($minPrice === null || $this->data[$childId]['price']['final_prices'] < $minPrice) {
                $minPrice = $this->data[$childId]['price'];
            }
        }

        $product['price'] = $minPrice;
    }

    protected function collectInventory(array &$product, int $id): void
    {
        $inventory = ['qty' => 0, 'status' => false, 'backorders' => false];

        if (isset($this->children[$id])) {
            foreach ($this->children[$id] as $childId => $null) {
                $childInventory = $this->data[$childId]['inventory'];
                $inventory['qty'] += $childInventory['qty'];
                $inventory['status'] = $inventory['status'] || $childInventory['status'];
                $inventory['backorders'] = $inventory['backorders'] || $childInventory['backorders'];
            }
        } else {
            $data = $this->inventory[$id] ?? $inventory;
            $inventory = [
                'qty' => (float)$data['qty'],
                'status' => (bool)$data['status'],
                'backorders' => (bool)$data['backorders'],
            ];
        }

        $product['inventory'] = $inventory;
    }

    protected function collectUrl(array &$product, int $id): void
    {
        if (!isset($this->urls[$id])) {
            return;
        }

        $url = $this->urls[$id];
        $isRelative = !preg_match('~^(https?://|/)~', $url);
        $product['url'] = $isRelative ? '/' . $url : $url;
    }

    protected function collectOptions(array &$product, int $id): void
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

    protected function collectGallery(array &$product, int $id): void
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

    protected function collectCategories(array &$product, int $id): void
    {
        $product['categories'] = $this->categories[$id] ?? [];
    }

    protected function collectRelatedIds(array &$product, int $id): void
    {
        $product['relatedIds'] = $this->relatedIds[$id] ?? [];
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

    protected function loadCategories(): void
    {
        $this->categories = $this->product->getCategoriesBatch($this->allIds);
    }

    protected function loadRelatedIds(): void
    {
        $this->relatedIds = $this->product->getLinkedProductsBatch($this->allIds, 'related', 25);
    }
}
