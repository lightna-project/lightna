<?php

declare(strict_types=1);

namespace Lightna\Frontend\Observer;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Lightna\Frontend\Model\Session\Manager as LightnaSessionManager;
use Lightna\Magento\Gen\Cart as LightnaCart;

class UpdateCart implements ObserverInterface
{
    public function __construct(
        protected LightnaSessionManager $lightnaSessionManager,
        protected CheckoutSession $checkoutSession,
    ) {
    }

    public function execute(Observer $observer): void
    {
        $quoteId = $this->checkoutSession->getQuoteId();
        $this->lightnaSessionManager->updateData(
            'cart',
            getobj(LightnaCart::class)->getData($quoteId),
        );
    }
}
