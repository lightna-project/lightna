<?php

declare(strict_types=1);

namespace Lightna\Magento\Index\Route;

use Exception;
use Laminas\Db\Sql\Select;
use Lightna\Engine\App\Database;
use Lightna\Engine\App\Entity\Route as RouteEntity;
use Lightna\Engine\App\Index\IndexAbstract;
use Lightna\Engine\App\Context;

class UrlRewrite extends IndexAbstract
{
    protected RouteEntity $entity;
    protected Database $db;
    protected Context $context;

    public function updateItem(string|int $id, array $url): void
    {
        if ($url['redirect_type'] == 0) {
            $type = match ($url['entity_type']) {
                'cms-page' => 'cms',
                'product' => 'product',
                'category' => 'category',
                default => null
            };
            if (!$type) {
                return;
            }

            $this->entity->addAction(
                $url['request_path'] === 'home' ? '' : $url['request_path'],
                'page',
                ['type' => $type, 'id' => $url['entity_id']]
            );
        } else {
            if ($url['redirect_type'] == 302) {
                $this->entity->add302($url['request_path'], $url['target_path']);
            } elseif ($url['redirect_type'] == 301) {
                $this->entity->add301($url['request_path'], $url['target_path']);
            } else {
                throw new Exception('Unhandled redirect type');
            }
        }
    }

    protected function removeItem(string|int $id): void
    {
        $this->entity->clean([$id]);
    }

    public function getBatchData(array $ids): array
    {
        return $this->db->fetch($this->getBatchSelect($ids), 'url_rewrite_id');
    }

    public function getBatchSelect(array $ids): Select
    {
        $select = $this->db
            ->select('url_rewrite')
            ->where(['store_id = ?' => $this->context->scope]);
        $select->where->in('url_rewrite_id', $ids);

        return $select;
    }

    public function scan(string|int $lastId = null): array
    {
        return $this->db->fetchCol($this->getScanSelect($lastId));
    }

    protected function getScanSelect(string|int $lastId = null): Select
    {
        $select = $this->db
            ->select('url_rewrite')
            ->columns(['url_rewrite_id'])
            ->where(['store_id = ?' => $this->context->scope])
            ->order('url_rewrite_id')
            ->limit(1000);

        $lastId && $select->where(['url_rewrite_id > ?' => $lastId]);

        return $select;
    }
}
