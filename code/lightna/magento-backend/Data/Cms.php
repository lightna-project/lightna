<?php

declare(strict_types=1);

namespace Lightna\Magento\Data;

use Lightna\Engine\App\Context\Entity\Loader as ContextEntityLoader;
use Lightna\Engine\Data\EntityData;

/**
 * @property-read string contentHeading
 * @property-read string content
 * @method string contentHeading
 * @method string content
 */
class Cms extends EntityData
{
    protected ContextEntityLoader $contextEntityLoader;

    protected function init(array $data = []): void
    {
        parent::init($this->contextEntityLoader->loadData());
    }
}
