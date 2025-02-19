<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Query;

use Laminas\Db\Sql\Select;
use Lightna\Engine\App\Context;
use Lightna\Engine\App\Entity\Route;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;

class Url extends ObjectA
{
    protected Database $db;
    protected Context $context;

    public function getEntityRoutesBatch(string $type, array $ids): array
    {
        $magentoType = $type === 'cms' ? 'cms-page' : $type;
        $routes = [];
        foreach ($this->getEntityUrlsBatch($magentoType, $ids) as $rewrite) {
            [$path, $route] = $this->rewriteToRoute($rewrite);
            $routes[$rewrite['entity_id']][$path] = $route;
        }

        return $routes;
    }

    public function rewriteToRoute(array $rewrite): array
    {
        $params = $rewrite['redirect_type'] === 0
            ? ['page', [
                'type' => $this->getRewriteRouteType($rewrite),
                'id' => $rewrite['entity_id'],
            ],]
            : [$rewrite['target_path']];

        return [
            $this->getRewriteRoutePath($rewrite),
            [
                $this->getRewriteRouteAction($rewrite),
                $params,
            ],
        ];
    }

    protected function getRewriteRouteType(array $rewrite): string
    {
        return match ($rewrite['entity_type']) {
            'cms-page' => 'cms',
            default => $rewrite['entity_type'],
        };
    }

    protected function getRewriteRoutePath(array $rewrite): string
    {
        return $rewrite['request_path'] === 'home' ? '' : $rewrite['request_path'];
    }

    protected function getRewriteRouteAction(array $rewrite): int
    {
        return match ($rewrite['redirect_type']) {
            301 => Route::ACTION_301,
            302 => Route::ACTION_302,
            default => Route::ACTION_CUSTOM,
        };
    }

    protected function getEntityUrlsBatch(string $entityType, array $ids): array
    {
        return $this->db->fetch($this->getEntityUrlsBatchSelect($entityType, $ids));
    }

    protected function getEntityUrlsBatchSelect(string $entityType, array $ids): Select
    {
        $select = $this->db->select()
            ->from(['u' => 'url_rewrite'])
            ->columns(['entity_type', 'entity_id', 'request_path', 'redirect_type', 'target_path'])
            ->where([
                'entity_type' => $entityType,
                'store_id' => $this->context->scope,
            ]);

        $select->where->in('entity_id', $ids);

        return $select;
    }

    public function getEntityDirectUrlsBatch(string $entityType, array $ids): array
    {
        return $this->db->fetchCol(
            $this->getEntityDirectUrlsBatchSelect($entityType, $ids),
            'request_path',
            'entity_id',
        );
    }

    protected function getEntityDirectUrlsBatchSelect(string $entityType, array $ids): Select
    {
        $select = $this->db->select()
            ->from(['u' => 'url_rewrite'])
            ->columns(['entity_id', 'request_path'])
            ->where([
                'entity_type' => $entityType,
                'store_id' => $this->context->scope,
                'redirect_type = ?' => 0,
            ]);

        $select->where->in('entity_id', $ids);

        return $select;
    }
}
