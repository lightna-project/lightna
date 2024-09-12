<?php

declare(strict_types=1);

namespace Lightna\Frontend\Model\Session;

use Lightna\Frontend\Model\Session as LightnaSession;
use Lightna\Magento\Producer\Cart as LightnaCart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Model\StoreManagerInterface;

class Manager
{
    public function __construct(
        protected CheckoutSession $checkoutSession,
        protected CustomerSession $customerSession,
        protected LightnaSession $lightnaSession,
        protected StoreManagerInterface $storeManager,
    ) {
    }

    public function updateData(): void
    {
        $this->updateCartData();
        $this->updateCustomerData();
    }

    public function updateCartData(): void
    {
        $this->updateSectionData(
            'cart',
            ($quoteId = $this->checkoutSession->getQuoteId())
                ? getobj(LightnaCart::class)->getData($quoteId)
                : [],
        );
    }

    public function updateCustomerData(): void
    {
        $this->updateSectionData(
            'user',
            [
                'groupId' => (int)$this->customerSession->getCustomerGroupId(),
            ],
        );
    }

    public function updateSectionData(string $section, array $data): void
    {
        $storeId = $this->storeManager->getStore()->getId();
        $lightnaData = $this->lightnaSession->getData();
        $lightnaData['data']['scope_' . $storeId][$section] = $data;
        $this->lightnaSession->setData($lightnaData);
    }
}
