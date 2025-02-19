<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data;

use Lightna\Engine\App\Context\Entity\Loader as ContextEntityLoader;
use Lightna\Engine\Data\EntityData;

/**
 * @method string name(string $escapeMethod = null)
 * @method string entityId(string $escapeMethod = null)
 */
class Category extends EntityData
{
    public string $name;
    public string|int $entityId;
    public ?string $image;
    public ?string $description;

    protected ContextEntityLoader $contextEntityLoader;

    protected function init(array $data = []): void
    {
        if (!$data) {
            parent::init($this->contextEntityLoader->loadData());
            $this->title = $this->name;
        } else {
            parent::init($data);
        }
    }
}
