<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Test\Unit\Index;

use Lightna\Magento\Backend\Index\Provider\Product;
use Lightna\PhpUnit\App\LightnaTestCase;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    use LightnaTestCase;

    public function testGetDataSimpleAvailable(): void
    {
        $subject = $this->newSubject(Product::class, [], $this->getSimpleProductDependenciesMock());

        $this->assertEquals(
            $this->getSimpleProductExpectedResult(),
            $subject->getData([1]),
        );
    }

    public function testGetDataSimpleAvailableByBackorders(): void
    {
        $depsMock = $this->getSimpleProductDependenciesMock();
        $depsMock['inventoryQuery']['getBatch'][1] = [
            'product_id' => 1,
            'qty' => 555,
            'status' => 0,
            'backorders' => 1,
        ];
        $subject = $this->newSubject(Product::class, [], $depsMock);

        $expected = $this->getSimpleProductExpectedResult();
        $expected[1]['inventory'] = [
            'qty' => 555.0,
            'status' => false,
            'backorders' => true,
        ];

        $this->assertEquals(
            $expected,
            $subject->getData([1]),
        );
    }

    public function testGetDataSimpleNotAvailableByStockStatus(): void
    {
        $depsMock = $this->getSimpleProductDependenciesMock();
        $depsMock['inventoryQuery']['getBatch'][1]['status'] = 0;
        $subject = $this->newSubject(Product::class, [], $depsMock);

        $this->assertEquals(
            [],
            $subject->getData([1]),
        );
    }

    public function testGetDataSimpleNotAvailableByMissingStock(): void
    {
        $depsMock = $this->getSimpleProductDependenciesMock();
        $depsMock['inventoryQuery']['getBatch'] = [];
        $subject = $this->newSubject(Product::class, [], $depsMock);

        $this->assertEquals(
            [],
            $subject->getData([1]),
        );
    }

    public function testGetDataSimpleNotAvailableByStatus(): void
    {
        $depsMock = $this->getSimpleProductDependenciesMock();

        // If product is disabled, it will have no price in the index
        $depsMock['product']['getPrices'] = [];

        $subject = $this->newSubject(Product::class, [], $depsMock);

        $this->assertEquals(
            [],
            $subject->getData([1]),
        );
    }

    public function testGetDataSimpleGroupPrices(): void
    {
        $depsMock = $this->getSimpleProductDependenciesMock();
        $depsMock['product']['getPrices'] = [
            ['entity_id' => 1, 'customer_group_id' => 0, 'final_price' => 100],
            ['entity_id' => 1, 'customer_group_id' => 5, 'final_price' => 90],
            ['entity_id' => 1, 'customer_group_id' => 11, 'final_price' => 80],
            ['entity_id' => 1, 'customer_group_id' => 20, 'final_price' => 100],
            ['entity_id' => 1, 'customer_group_id' => 30, 'final_price' => 100],
        ];

        $expected = $this->getSimpleProductExpectedResult();
        $expected[1]['price'] = [
            'regular' => 110.0,
            'final_prices' => [100.0, 5 => 90.0, 11 => 80.0],
            'discounts' => [10.0, 5 => 20.0, 11 => 30.0],
            'discount_percents' => [9.0, 5 => 18.0, 11 => 27.0],
        ];

        $subject = $this->newSubject(Product::class, [], $depsMock);

        $this->assertEquals(
            $expected,
            $subject->getData([1]),
        );
    }

    public function testGetDataConfigurableAvailable(): void
    {
        $depsMock = $this->getConfigurableProductDependenciesMock();
        $subject = $this->newSubject(Product::class, [], $depsMock);

        $this->assertEquals(
            [
                4 => [
                    'entity_id' => 4,
                    'attribute_set_id' => 2,
                    'type_id' => 'configurable',
                    'sku' => 'SKU123',
                    'name' => 'Product Name 4',
                    'price' => [
                        'regular' => 110.0,
                        'final_prices' => [100.0],
                        'discounts' => [10.0],
                        'discount_percents' => [9.0],
                    ],
                    'inventory' => [
                        'qty' => 1665.0,
                        'status' => true,
                        'backorders' => false,
                    ],
                    'options' => [
                        'attributes' =>
                            [['id' => 60, 'code' => 'size', 'label' => 'Size']],
                        'variants' => [
                            [
                                'product_id' => 1,
                                'values' => [['code' => 'size', 'label' => 'S', 'id' => 151]],
                            ],
                            [
                                'product_id' => 2,
                                'values' => [['code' => 'size', 'label' => 'M', 'id' => 152]],
                            ],
                            [
                                'product_id' => 3,
                                'values' => [['code' => 'size', 'label' => 'L', 'id' => 153]],
                            ],
                        ],
                    ],
                    'gallery' => [['max' => 'coming-soon.jpg']],
                    'categories' => [],
                ],
            ],
            $subject->getData([1, 2, 3, 4]),
        );
    }

    protected function getSimpleProductDependenciesMock(): array
    {
        return [
            'product' => [
                'getBatch' => [
                    1 => ['entity_id' => 1, 'attribute_set_id' => 1, 'type_id' => 'simple', 'sku' => 'SKU123'],
                ],
                'getPrices' => [
                    ['entity_id' => 1, 'customer_group_id' => 0, 'final_price' => 100],
                ],
            ],
            'eav' => [
                'getAttributeValues' => [
                    1 => ['name' => 'Product Name 1', 'price' => 110],
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
        ];
    }

    protected function getConfigurableProductDependenciesMock(): array
    {
        return [
            'product' => [
                'getBatch' => [
                    1 => ['entity_id' => 1, 'attribute_set_id' => 1, 'type_id' => 'virtual', 'sku' => 'SKU123-S'],
                    2 => ['entity_id' => 2, 'attribute_set_id' => 1, 'type_id' => 'virtual', 'sku' => 'SKU123-M'],
                    3 => ['entity_id' => 3, 'attribute_set_id' => 1, 'type_id' => 'virtual', 'sku' => 'SKU123-L'],
                    4 => ['entity_id' => 4, 'attribute_set_id' => 2, 'type_id' => 'configurable', 'sku' => 'SKU123'],
                ],
                'getPrices' => [
                    ['entity_id' => 1, 'customer_group_id' => 0, 'final_price' => 100],
                    ['entity_id' => 2, 'customer_group_id' => 0, 'final_price' => 100],
                    ['entity_id' => 3, 'customer_group_id' => 0, 'final_price' => 100],
                    ['entity_id' => 4, 'customer_group_id' => 0, 'final_price' => 0],
                ],
                'getChildrenRelations' => [
                    ['parent_id' => 4, 'child_id' => 1],
                    ['parent_id' => 4, 'child_id' => 2],
                    ['parent_id' => 4, 'child_id' => 3],
                ],
                'getConfigurableOptions' => [
                    ['product_id' => 4, 'attribute_id' => 60, 'code' => 'size', 'label' => 'Size'],
                ],
            ],
            'eav' => [
                'getAttributeValues' => [
                    1 => ['name' => 'Product Name 1', 'price' => 110, 'size' => 'S'],
                    2 => ['name' => 'Product Name 2', 'price' => 110, 'size' => 'M'],
                    3 => ['name' => 'Product Name 3', 'price' => 110, 'size' => 'L'],
                    4 => ['name' => 'Product Name 4'],
                ],
                'getAttributeValuesRaw' => [
                    1 => ['size' => ['entity_id' => 1, 'value' => 151]],
                    2 => ['size' => ['entity_id' => 2, 'value' => 152]],
                    3 => ['size' => ['entity_id' => 3, 'value' => 153]],
                ],
            ],
            'gallery' => [],
            'inventoryQuery' => [
                'getBatch' => [
                    1 => ['product_id' => 1, 'qty' => 555, 'status' => 1, 'backorders' => 0],
                    2 => ['product_id' => 2, 'qty' => 555, 'status' => 1, 'backorders' => 0],
                    3 => ['product_id' => 3, 'qty' => 555, 'status' => 1, 'backorders' => 0],
                ],
            ],
            'urlQuery' => [
                'getEntityDirectUrlsBatch' => [],
            ],
            'rawValueAttributes' => [],
        ];
    }

    protected function getSimpleProductExpectedResult(): array
    {
        return [
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
                'categories' => [],
            ],
        ];
    }
}
