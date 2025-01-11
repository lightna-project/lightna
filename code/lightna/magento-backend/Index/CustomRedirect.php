<?php

declare(strict_types=1);

namespace Lightna\Magento\Index;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\Context;
use Lightna\Engine\App\Entity\Route as RouteEntity;
use Lightna\Engine\App\Index\IndexAbstract;
use Lightna\Engine\App\Project\Database;
use Lightna\Magento\App\Query\Url;

class CustomRedirect extends IndexAbstract
{
    protected RouteEntity $entity;
    protected Database $db;
    protected Context $context;
    protected Url $url;
    protected bool $hasRoutes = true;

    public function getDataBatch(array $ids): array
    {
        return $this->db->fetch($this->getBatchSelect($ids), 'url_rewrite_id');
    }

    public function getRoutesBatch(array $ids): array
    {
        $routes = [];
        foreach ($this->dataBatch as $id => $rewrite) {
            [$path, $route] = $this->url->rewriteToRoute($rewrite);
            $routes[$id][$path] = $route;
        }

        return $routes;
    }

    protected function getBatchSelect(array $ids): Select
    {
        $select = $this->db->select('url_rewrite');
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
            ->where([
                'store_id = ?' => $this->context->scope,
                'entity_id = ?' => 0,
                'redirect_type <> ?' => 0,
            ])
            ->order('url_rewrite_id')
            ->limit(1000);

        $lastId && $select->where(['url_rewrite_id > ?' => $lastId]);

        return $select;
    }

    protected function updateItem(string|int $id, array $data): void
    {
        // Skip data, index routes only
    }

    protected function removeItem(string|int $id): void
    {
        // Skip data, index routes only
    }

    public function gcCheck(array $ids): array
    {
        $exists = $this->db->fetchCol($this->getGcCheckSelect($ids));

        // Home page URL always exists
        $exists[] = '';

        return array_diff($ids, $exists);
    }

    protected function getGcCheckSelect(array $urls): Select
    {
        $select = $this->db->select()
            ->columns(['request_path'])
            ->from('url_rewrite')
            ->where(['store_id = ?' => $this->context->scope]);
        $select->where->in('request_path', $urls);

        return $select;
    }
}
