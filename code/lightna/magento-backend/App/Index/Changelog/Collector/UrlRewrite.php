<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Index\Changelog\Collector;

use Lightna\Engine\App\Index\Changelog\Collect;
use Lightna\Engine\App\Index\Changelog\CollectorInterface;
use Lightna\Engine\App\ObjectA;

class UrlRewrite extends ObjectA implements CollectorInterface
{
    protected Collect $collect;
    protected array $entityIdsByType;

    public function collect(string $table, array $changelog): array
    {
        if ($table === 'url_rewrite') {
            $toQueue = [];
            $this->entityIdsByType = $this->getEntityIdsByType($changelog);

            if ($redirectIds = $this->entityIdsByType['custom_redirect'] ?? []) {
                $toQueue['custom_redirect'] = $redirectIds;
            }
            if ($productIds = $this->entityIdsByType['product'] ?? []) {
                $toQueue['product'] = $productIds;
            }
            if (isset($this->entityIdsByType['category'])) {
                $toQueue['content_page'] = [1]; // Update Top Menu
            }
            if ($cmsPageIds = $this->entityIdsByType['cms-page'] ?? []) {
                $toQueue['cms'] = $cmsPageIds;
            }

            return $toQueue;
        }

        return [];
    }

    protected function getEntityIdsByType(array $changelog): array
    {
        $result = [];
        foreach ($changelog as $record) {
            $indexType = $this->getRecordIndexType($record);
            $entityIdColumn = $indexType === 'custom_redirect' ? 'url_rewrite_id' : 'entity_id';
            $entityIds = $this->collect->recordValues($record, $entityIdColumn);
            foreach ($entityIds as $entityId) {
                $result[$indexType][$entityId] = (int)$entityId;
            }
        }

        return $result;
    }

    protected function getRecordIndexType(array $record): string
    {
        // Ignore case when entity type changed
        $entityType = current($this->collect->recordValues($record, 'entity_type'));

        return match ($entityType) {
            'custom' => 'custom_redirect',
            'cms_page' => 'cms',
            default => $entityType,
        };
    }
}
