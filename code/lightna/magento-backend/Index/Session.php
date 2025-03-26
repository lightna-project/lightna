<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Index;

use Lightna\Engine\App\ObjectA;
use Lightna\Magento\Backend\App\Entity\Product as ProductEntity;
use Lightna\Magento\Backend\App\Query\Quote;
use Lightna\Magento\Backend\App\Query\Wishlist;
use Lightna\Magento\Backend\Data\Product\Gallery\Image;

class Session extends ObjectA
{
    protected Quote $quote;
    protected Wishlist $wishlist;
    protected ProductEntity $productEntity;
    /** @AppConfig(cart/item/fields) */
    protected array $itemFields;

    public function getData(array $sessionData): array
    {
        return array_camel([
            'customer' => $this->getCustomerData($sessionData),
            'cart' => $this->getCartData($sessionData),
            'wishlist' => $this->getWishlistData($sessionData),
        ]);
    }

    protected function getCustomerData(array $sessionData): array
    {
        return [
            'group_id' => $sessionData['customer_group_id'] ?? 0,
        ];
    }

    protected function getCartData(array $sessionData): array
    {
        $quoteId = $sessionData['quote_id'] ?? 0;

        if (
            !$quoteId
            || (!$quote = $this->quote->get($quoteId))
            || $quote['is_active'] !== 1
            || !$itemRows = $this->quote->getItems($quoteId)
        ) {
            return [];
        }

        return array_camel([
            'qty' => (int)$quote['items_qty'],
            'grand_total' => (float)$quote['grand_total'],
            'items' => $this->buildCartItems($itemRows),
        ]);
    }

    protected function buildCartItems(array $rows): array
    {
        $items = $this->getItemsData($rows);
        $this->addProductsData($items);

        return $items;
    }

    protected function getItemsData(array $rows): array
    {
        $items = [];
        foreach ($rows as $i => $row) {
            $items[$i] = array_intersect_key($row, array_flip($this->getItemFields()));
            $items[$i]['qty'] += 0;
        }

        return $items;
    }

    protected function getItemFields(): array
    {
        return ['product_id', ...$this->itemFields];
    }

    protected function addProductsData(array &$items): void
    {
        $ids = array_map(fn($item) => $item['product_id'], $items);
        $products = $this->productEntity->getList($ids);
        foreach ($items as &$item) {
            if (!$products[$item['product_id']]) continue;
            $this->addProductData($item, $products[$item['product_id']]);
        }
    }

    protected function addProductData(array &$item, array $product): void
    {
        $thumbnail = newobj(Image::class, reset($product['gallery']))->thumbnail;

        $item = merge($item, [
            'url' => $product['url'],
            'thumbnail' => $thumbnail,
        ]);
    }

    protected function getWishlistData(array $sessionData): array
    {
        if (!$customerId = ($sessionData['customer_id'] ?? null)) {
            return [];
        }

        return [
            'items' => $this->wishlist->getItems($customerId),
        ];
    }
}
