<?php

declare(strict_types=1);

namespace Lightna\Frontend\Model\Session;

use Lightna\Frontend\Model\Session as LightnaSession;
use Lightna\Session\App\Session\DataBuilder as LightnaSessionDataBuilder;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;

class Manager
{
    public function __construct(
        protected CheckoutSession $checkoutSession,
        protected CustomerSession $customerSession,
        protected LightnaSession $lightnaSession,
    ) {
    }

    public function updateData(bool $forceReindex = false): void
    {
        $dataBuilder = getobj(LightnaSessionDataBuilder::class)
            ->setSessionData($this->lightnaSession->getData())
            ->setField('customer_id', (int)$this->customerSession->getId())
            ->setField('quote_id', (int)$this->checkoutSession->getQuoteId())
            ->setField('customer_group_id', (int)$this->customerSession->getCustomerGroupId())
            ->forceReindex($forceReindex);

        $this->lightnaSession->setData($dataBuilder->getSessionData());
    }
}
