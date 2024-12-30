<?php

declare(strict_types=1);

namespace Lightna\Magento\Test\Unit\Index;

use Lightna\Magento\Index\Provider\Product;
use Lightna\PhpUnit\App\LightnaTestCase;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    use LightnaTestCase;

    public function testGetDataSimpleAvailable(): void
    {
        $subject = $this->newSubject(Product::class, [
            'product' => [
                'getBatch' => [
                    1 => ['entity_id' => 1, 'attribute_set_id' => 1, 'type_id' => 'simple', 'sku' => 'SKU123']
                ],
                'getPrices' => [
                    ['entity_id' => 1, 'customer_group_id' => 0, 'final_price' => 100],
                ],
            ],
            'eav' => [
                'getAttributeValues' => [
                    1 => ['name' => 'Product Name 1', 'price' => 110]
                ],
            ],
            'gallery' => [],
            'inventoryQuery' => [
                'getBatch' => [
                    1 => ['product_id' => 1, 'qty' => 555, 'status' => 1, 'backorders' => 0],
                ],
            ],
            'urlQuery' => [
                'getEntityDirectUrlsBatch' => [],
            ],
            'rawValueAttributes' => [],
        ]);

        $this->assertEquals(
            [
                1 => [
                    'entity_id' => 1,
                    'attribute_set_id' => 1,
                    'type_id' => 'simple',
                    'sku' => 'SKU123',
                    'name' => 'Product Name 1',
                    'price' => [
                        'regular' => 110.0,
                        'final_prices' => [100.0],
                        'discounts' => [10.0],
                        'discount_percents' => [9.0],
                    ],
                    'inventory' => [
                        'qty' => 555.0,
                        'status' => true,
                        'backorders' => false,
                    ],
                    'options' => ['attributes' => []],
                    'gallery' => [['max' => 'coming-soon.jpg']],
                ],
            ],
            $subject->getData([1])
        );
    }
}
