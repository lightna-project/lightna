<?php

declare(strict_types=1);

namespace Lightna\Frontend\Model\Adminhtml\Index;

use DateTime;
use IntlDateFormatter;
use Lightna\Engine\App\Indexer as LightnaIndexer;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class DataSource extends Collection
{
    public function __construct(
        EntityFactory $entityFactory,
        protected TimezoneInterface $timezone,
        protected LightnaIndexer $lightnaIndexer,
    ) {
        parent::__construct($entityFactory);

        $this->lightnaIndexer = getobj(LightnaIndexer::class);
    }

    public function loadData($printQuery = false, $logQuery = false): DataSource
    {
        if (!$this->isLoaded()) {
            foreach ($this->getIndexItems() as $type) {
                $this->addItem($type);
            }
            $this->_setIsLoaded();
        }

        return $this;
    }

    protected function getIndexItems(): array
    {
        $items = [];
        foreach ($this->lightnaIndexer->getIndexState()->entities as $code => $index) {
            $items[$code] = new DataObject([
                'id' => $code,
                'name' => strtoupper($code),
                'status' => (int)$index->isUpToDate(),
                'rebuilt_at' => $this->timeToDate($index->rebuiltAt),
                'invalidated_at' => $this->timeToDate($index->invalidatedAt),
            ]);
        }

        return $items;
    }

    protected function timeToDate(float|int|null $time): string
    {
        return $time ?
            $this->timezone->formatDateTime(
                (new DateTime())->setTimestamp((int)$time),
                IntlDateFormatter::LONG,
                true
            ) :
            '-';
    }
}
