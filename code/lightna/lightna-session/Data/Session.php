<?php

declare(strict_types=1);

namespace Lightna\Session\Data;

use Lightna\Engine\Data\DataA;
use Lightna\Session\App\Session as AppSession;

class Session extends DataA
{
    protected AppSession $appSession;

    protected function init(array $data = []): void
    {
        $data = $this->appSession->read();
        parent::init($data);
    }
}
