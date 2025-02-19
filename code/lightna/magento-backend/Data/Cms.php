<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data;

use Lightna\Engine\App\Context\Entity\Loader as ContextEntityLoader;
use Lightna\Engine\Data\EntityData;

/**
 * @method string contentHeading(string $escapeMethod = null)
 * @method string content(string $escapeMethod = null)
 */
class Cms extends EntityData
{
    public string $contentHeading;
    public string $content;

    protected ContextEntityLoader $contextEntityLoader;

    protected function init(array $data = []): void
    {
        parent::init($this->contextEntityLoader->loadData());
    }
}
