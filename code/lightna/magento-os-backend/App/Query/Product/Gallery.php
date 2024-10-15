<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Query\Product;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;
use Lightna\Magento\App\Query\Config as MagentoConfig;

class Gallery extends ObjectA
{
    protected Database $db;
    protected MagentoConfig $magentoConfig;
    protected string $cryptKey;
    protected string $imageQuality;

    protected function defineCryptKey(): void
    {
        $this->cryptKey = $this->magentoConfig->getValue('crypt/key');
    }

    protected function defineImageQuality(): void
    {
        $this->imageQuality = $this->magentoConfig->getValue('system/upload_configuration/jpeg_quality');
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
            ->where('gallery.attribute_id = 90 and gallery.media_type = "image" and gallery.disabled = 0');

        $select->where->in('entity.entity_id', $entityIds);

        return $select;
    }

    public function getCompressedTypes(string $image): array
    {
        return [
            'tile' => $this->getHash(285, 354) . $image,
            'preview' => $this->getHash(535, 664) . $image,
            'thumbnail' => $this->getHash(100, 124) . $image,
        ];
    }

    protected function getHash(int $w, int $h): string
    {
        return hash_hmac(
            'md5',
            "h:{$h}_w:{$w}_rgb255,255,255_r:empty_q:{$this->imageQuality}_proportional_frame_transparency_doconstrainonly",
            $this->cryptKey,
            false
        );
    }
}
