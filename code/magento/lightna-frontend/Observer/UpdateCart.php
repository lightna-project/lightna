<?php

declare(strict_types=1);

namespace Lightna\Frontend\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Lightna\Frontend\Model\Session\Manager as LightnaSessionManager;

class UpdateCart implements ObserverInterface
{
    public function __construct(
        protected LightnaSessionManager $lightnaSessionManager,
    ) {
    }

    public function execute(Observer $observer): void
    {
        $this->lightnaSessionManager->updateCartData();
    }
}
