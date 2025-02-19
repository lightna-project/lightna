<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Query\Product;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;
use Lightna\Magento\Backend\App\Query\Config as MagentoConfig;

class Gallery extends ObjectA
{
    protected Database $db;
    protected MagentoConfig $magentoConfig;
    /** @AppConfig(backend:indexer/product/image) */
    protected array $imageConfig;

    protected string $cryptKey;
    protected string $imageQuality;
    protected int $galleryAttributeId;

    /** @noinspection PhpUnused */
    protected function defineCryptKey(): void
    {
        $this->cryptKey = $this->magentoConfig->getValue('crypt/key');
    }

    /** @noinspection PhpUnused */
    protected function defineImageQuality(): void
    {
        $this->imageQuality = $this->magentoConfig->getValue('system/upload_configuration/jpeg_quality');
    }

    protected function defineGalleryAttributeId(): void
    {
        $this->galleryAttributeId = $this->db->fetchOneCol($this->getGalleryAttributeIdSelect());
    }

    protected function getGalleryAttributeIdSelect(): Select
    {
        return $this->db->select()
            ->from('eav_attribute')
            ->where([
                'entity_type_id = ?' => 4,
                'attribute_code = ?' => 'media_gallery',
            ]);
    }

    public function getItems(array $entityIds): array
    {
        $items = [];
        foreach ($this->db->fetch($this->getItemsSelect($entityIds)) as $row) {
            $items[$row['entity_id']][] = $row['value'];
        }

        return $items;
    }

    protected function getItemsSelect(array $entityIds): Select
    {
        $select = $this->db->select()
            ->from(['entity' => 'catalog_product_entity'])
            ->columns(['entity_id'])
            ->join(
                ['g2e' => 'catalog_product_entity_media_gallery_value_to_entity'],
                'g2e.entity_id = entity.entity_id',
                [])
            ->join(
                ['gallery' => 'catalog_product_entity_media_gallery'],
                'gallery.value_id = g2e.value_id')
            ->where([
                'gallery.attribute_id = ?' => $this->galleryAttributeId,
                'gallery.media_type = "image"',
                'gallery.disabled = 0',
            ]);

        $select->where->in('entity.entity_id', $entityIds);

        return $select;
    }

    public function getCompressedTypes(string $image): array
    {
        return [
            'tile' => $this->getHash($this->imageConfig['tile']['width'], $this->imageConfig['tile']['height']) . $image,
            'thumbnail' => $this->getHash($this->imageConfig['thumbnail']['width'], $this->imageConfig['thumbnail']['height']) . $image,
            'preview' => $this->getHash($this->imageConfig['preview']['width'], $this->imageConfig['preview']['height']) . $image,
        ];
    }

    protected function getHash(int $w, int $h): string
    {
        // \Magento\Catalog\Model\View\Asset\Image::getMiscPath to compare
        return hash_hmac(
            'md5',
            "h:{$h}_w:{$w}_rgb255,255,255_r:empty_q:{$this->imageQuality}_proportional_frame_transparency_doconstrainonly",
            $this->cryptKey,
            false
        );
    }
}
