<?php

declare(strict_types=1);

namespace Lightna\Magento\Index\Provider;

use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;
use Lightna\Magento\App\Query\Inventory;
use Lightna\Magento\App\Query\Product\Eav;
use Lightna\Magento\App\Query\Product\Gallery;
use Lightna\Magento\App\Query\Store;
use Lightna\Magento\App\Query\Url;
use Lightna\Magento\Index\Product as ProductIndex;

class Product extends ObjectA
{
    protected Database $db;
    protected ProductIndex $productIndex;
    protected Store $store;
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
    /** Result */
    protected array $data = [];

    public function getData(array $ids): array
    {
        $this->ids = $this->allIds = $ids;
        $this->addRelations();
        $this->loadData();

        $batchSelect = $this->productIndex->getBatchSelect($this->allIds);
        foreach ($this->db->fetch($batchSelect, 'entity_id') as $id => $product) {
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

        $select = $this->db->select('catalog_product_relation');
        $select->where->in('parent_id', $this->ids);

        foreach ($this->db->fetch($select) as $row) {
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
        $websiteId = $this->store->getWebsiteId();

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
}
