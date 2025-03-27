<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data;

use Lightna\Engine\App\Context\Entity\Loader as ContextEntityLoader;
use Lightna\Engine\Data\EntityData;
use Lightna\Magento\Backend\Data\Product\Gallery\Image;
use Lightna\Magento\Backend\Data\Product\Inventory as ProductInventory;
use Lightna\Magento\Backend\Data\Product\Options as ProductOptions;
use Lightna\Magento\Backend\Data\Product\Price as ProductPrice;

/**
 * @property Image[] gallery
 * @method string attributeSetId(string $escapeMethod = null)
 * @method string children(string $escapeMethod = null)
 * @method string categories(string $escapeMethod = null)
 * @method string related(string $escapeMethod = null)
 * @method string description(string $escapeMethod = null)
 * @method string entityId(string $escapeMethod = null)
 * @method string name(string $escapeMethod = null)
 * @method string price(string $escapeMethod = null)
 * @method string shortDescription(string $escapeMethod = null)
 * @method string sku(string $escapeMethod = null)
 * @method string inventory(string $escapeMethod = null)
 * @method string typeId(string $escapeMethod = null)
 * @method string url(string $escapeMethod = null)
 */
class Product extends EntityData
{
    public ProductPrice $price;
    public ProductInventory $inventory;
    public ProductOptions $options;
    public array $categories;
    public array $gallery;
    public int $attributeSetId;
    public int $entityId;
    public string $description = '';
    public string $name;
    public string $shortDescription;
    public string $sku;
    public string $typeId;
    public string $url;
    public array $related = [];

    protected ContextEntityLoader $contextEntityLoader;

    protected function init(array $data = []): void
    {
        parent::init($this->getData($data));
        $this->title = $this->name;
    }

    protected function getData(array $data): array
    {
        return $data ?: $this->contextEntityLoader->loadData();
    }
}
