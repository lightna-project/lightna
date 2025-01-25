<?php

declare(strict_types=1);

namespace Lightna\Session\Data;

use Lightna\Engine\Data\DataA;
use Lightna\Session\App\Session as AppSession;

class Session extends DataA
{
    public bool $isReindexRequired;
    protected AppSession $appSession;

    protected function init(array $data = []): void
    {
        if ($this->appSession->canRead()) {
            $data = $this->appSession->getData();
            $data = $data['index'] ?? [];
        }

        parent::init($data);
    }

    /** @noinspection PhpUnused */
    protected function defineIsReindexRequired(): void
    {
        $this->isReindexRequired = $this->appSession->getIsReindexRequired();
    }
}
