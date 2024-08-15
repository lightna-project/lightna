<?php

declare(strict_types=1);

namespace Lightna\Frontend\Model\Session;

use Magento\Store\Model\StoreManagerInterface;
use Lightna\Frontend\Model\Session as LightnaSession;

class Manager
{
    public function __construct(
        protected LightnaSession $lightnaSession,
        protected StoreManagerInterface $storeManager,
    ) {
    }

    public function updateData(string $key, mixed $data): void
    {
        $session = $this->lightnaSession->getData();
        $storeId = $this->storeManager->getStore()->getId();
        $session['data']['scope_' . $storeId][$key] = $data;
        $this->lightnaSession->setData($session);
    }
}
