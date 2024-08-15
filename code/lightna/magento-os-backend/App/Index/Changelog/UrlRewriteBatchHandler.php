<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Index\Changelog;

use Lightna\Engine\App\Index\Changelog\BatchHandlerAbstract;

class UrlRewriteBatchHandler extends BatchHandlerAbstract
{
    public function handle(string $table, array $changelog): array
    {
        if ($table === 'url_rewrite') {
            $toQueue = [];
            $this->collectUrlRewriteChanges($changelog, $toQueue);
            $this->collectProductChanges($changelog, $toQueue);
            $this->collectContentPageChanges($changelog, $toQueue);

            return $toQueue;
        }

        return [];
    }

    protected function collectUrlRewriteChanges(array $changelog, array &$toQueue): void
    {
        $toQueue['url_rewrite'] = $this->collectIds($changelog, 'url_rewrite_id');
    }

    protected function collectProductChanges(array $changelog, array &$toQueue): void
    {
        foreach ($changelog as $record) {
            if (in_array('product', $this->collectRecordValues($record, 'entity_type'))) {
                foreach ($this->collectRecordIds($record, 'entity_id') as $id) {
                    $toQueue['product'][$id] = $id;
                }
            }
        }
    }

    /**
     * If category url changed, reindex Menu
     */
    protected function collectContentPageChanges(array $changelog, array &$toQueue): void
    {
        foreach ($changelog as $record) {
            if (in_array('category', $this->collectRecordValues($record, 'entity_type'))) {
                $toQueue['content_page'] = [1]; // Update Top Menu
                return;
            }
        }
    }
}
