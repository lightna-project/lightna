<?php

declare(strict_types=1);

namespace Lightna\Frontend\Plugin;

use Lightna\Frontend\Model\Session\Manager as LightnaSessionManager;

class CheckoutSession
{
    public function __construct(
        protected LightnaSessionManager $lightnaSessionManager,
    ) {
    }

    public function afterSetQuoteId(): void
    {
        $this->lightnaSessionManager->updateData();
    }
}
