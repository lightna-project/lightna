<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Test\Unit\App\Query;

use Lightna\Magento\Backend\App\Query\Product;
use Lightna\PhpUnit\App\LightnaTestCase;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    use LightnaTestCase;

    public function testAvailableTypesOrder(): void
    {
        $types = $this->newSubject(Product::class)->getAvailableTypes();
        $simplePosition = array_search('simple', $types);
        $virtualPosition = array_search('virtual', $types);
        $configurablePosition = array_search('configurable', $types);

        $this->assertGreaterThan($simplePosition, $configurablePosition);
        $this->assertGreaterThan($virtualPosition, $configurablePosition);
    }
}
