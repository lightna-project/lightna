<?php

declare(strict_types=1);

namespace Lightna\Frontend\Model;

use Magento\Framework\View\Layout as MagentoLayout;

class Layout extends MagentoLayout
{
    public function renderElement($name, $useCache = true): string
    {
        if ($lightnaBlockId = $this->getLightnaBlockId($name)) {
            return $this->renderLightnaBlock($lightnaBlockId);
        } else {
            return parent::renderElement($name, $useCache);
        }
    }

    protected function getLightnaBlockId(string $name): ?string
    {
        $isLightnaBlock = $this->isBlock($name)
            && ($block = $this->getBlock($name))
            && ($lightnaBlockId = $block->getLightnaBlockId());

        return $isLightnaBlock ? $lightnaBlockId : null;
    }

    protected function renderLightnaBlock(string $blockId): string
    {
        ob_start();
        block('#' . $blockId);

        return ob_get_clean();
    }
}
