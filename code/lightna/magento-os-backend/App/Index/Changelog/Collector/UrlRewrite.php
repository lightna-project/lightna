<?php

declare(strict_types=1);

namespace Lightna\Magento\App\Index\Changelog\Collector;

use Lightna\Engine\App\Index\Changelog\Collect;
use Lightna\Engine\App\Index\Changelog\CollectorInterface;
use Lightna\Engine\App\ObjectA;

class UrlRewrite extends ObjectA implements CollectorInterface
{
    protected Collect $collect;

    public function collect(string $table, array $changelog): array
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
        $toQueue['url_rewrite'] = $this->collect->ids($changelog, 'url_rewrite_id');
    }

    protected function collectProductChanges(array $changelog, array &$toQueue): void
    {
        foreach ($changelog as $record) {
            if (in_array('product', $this->collect->recordValues($record, 'entity_type'))) {
                foreach ($this->collect->recordIds($record, 'entity_id') as $id) {
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
            if (in_array('category', $this->collect->recordValues($record, 'entity_type'))) {
                $toQueue['content_page'] = [1]; // Update Top Menu
                return;
            }
        }
    }
}
