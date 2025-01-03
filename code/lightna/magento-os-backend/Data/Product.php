<?php

declare(strict_types=1);

namespace Lightna\Magento\Data;

use Lightna\Engine\App\Context;
use Lightna\Engine\App\NotFoundException;
use Lightna\Engine\Data\EntityData;
use Lightna\Magento\Data\Product\Gallery\Image;
use Lightna\Magento\Data\Product\Inventory as ProductInventory;
use Lightna\Magento\Data\Product\Options as ProductOptions;
use Lightna\Magento\Data\Product\Price as ProductPrice;

/**
 * @property-read Image[] gallery
 * @method string attributeSetId(string $escapeMethod = null)
 * @method string children(string $escapeMethod = null)
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
    public array $gallery;
    public int $attributeSetId;
    public int $entityId;
    public string $description = '';
    public string $name;
    public string $shortDescription;
    public string $sku;
    public string $typeId;
    public string $url;

    /** @AppConfig(entity/product/entity) */
    protected string $productEntity;
    protected Context $context;

    protected function init(array $data = []): void
    {
        parent::init($this->getData($data));
        $this->title = $this->name;
    }

    protected function getData(array $data): array
    {
        return $data ?: $this->getEntityData();
    }

    protected function getEntityData(): array
    {
        if ($this->context->entity->type !== 'product') {
            throw new \Exception(
                'Attempt to load product entity when rendering ' . $this->context->entity->type
            );
        }

        $entity = getobj($this->productEntity);

        if (
            !$this->context->entity->id
            || !($entityData = $entity->get($this->context->entity->id))
        ) {
            throw new NotFoundException(
                'Entity "' . $this->context->entity->type . ':' . $this->context->entity->id . '" not found'
            );
        }

        return $entityData;
    }
}
