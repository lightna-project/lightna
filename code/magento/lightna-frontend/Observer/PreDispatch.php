<?php

declare(strict_types=1);

namespace Lightna\Frontend\Observer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class PreDispatch implements ObserverInterface
{
    public function __construct(
        protected ResourceConnection $resource,
    ) {
    }

    public function execute(Observer $observer): void
    {
        $GLOBALS['SHARED_PDO_CONNECTION'] = $this->resource->getConnection()->getConnection();
    }
}
