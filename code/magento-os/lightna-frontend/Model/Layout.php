<?php

declare(strict_types=1);

namespace Lightna\Frontend\Model;

use Lightna\Engine\App\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Framework\View\Layout as MagentoLayout;

class Layout extends MagentoLayout
{
    protected bool $isLightnaPageContextInitialized = false;
    protected const LAYOUT_LIGHTNA_TYPE = [
        'catalog_product_view' => 'product',
        'catalog_category_view' => 'category',
    ];

    public function renderElement($name, $useCache = true): string
    {
        $this->initLightnaPageContext();

        if ($lightnaBlockId = $this->getLightnaBlockId($name)) {
            return blockhtml('#' . $lightnaBlockId);
        } else {
            return parent::renderElement($name, $useCache);
        }
    }

    protected function initLightnaPageContext(): void
    {
        if ($this->isLightnaPageContextInitialized) {
            return;
        }

        $context = getobj(Context::class);
        $type = $this->getLightnaEntityType();
        $context->entity->type = $type;
        $context->entity->id = $this->getLightnaEntityId($type);
        $context->mode = 'semi';

        $this->isLightnaPageContextInitialized = true;
    }

    protected function getLightnaEntityType(): string
    {
        foreach ($this->getUpdate()->getHandles() as $handle) {
            if (isset(static::LAYOUT_LIGHTNA_TYPE[$handle])) {
                return static::LAYOUT_LIGHTNA_TYPE[$handle];
            }
        }

        return 'page';
    }

    protected function getLightnaEntityId(string $type): ?int
    {
        return match ($type) {
            'product' => $this->getCurrentProductId(),
            'category' => $this->getCurrentCategoryId(),
            default => null,
        };
    }

    protected function getCurrentProductId(): int
    {
        return (int)ObjectManager::getInstance()->get(Registry::class)
            ->registry('product')?->getId();
    }

    protected function getCurrentCategoryId(): int
    {
        return (int)ObjectManager::getInstance()->get(Registry::class)
            ->registry('current_category')?->getId();
    }

    protected function getLightnaBlockId(string $name): ?string
    {
        $isLightnaBlock = $this->isBlock($name)
            && ($block = $this->getBlock($name))
            && ($lightnaBlockId = $block->getLightnaBlockId());

        return $isLightnaBlock ? $lightnaBlockId : null;
    }
}
