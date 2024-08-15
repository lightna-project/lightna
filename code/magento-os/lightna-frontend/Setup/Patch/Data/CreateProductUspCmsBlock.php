<?php

declare(strict_types=1);

namespace Lightna\Frontend\Setup\Patch\Data;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterfaceFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateProductUspCmsBlock implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var BlockInterfaceFactory
     */
    private $blockFactory;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param BlockInterfaceFactory $blockFactory
     * @param BlockRepositoryInterface $blockRepository
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        BlockInterfaceFactory $blockFactory,
        BlockRepositoryInterface $blockRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockFactory = $blockFactory;
        $this->blockRepository = $blockRepository;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        foreach ($this->getContent() as $blockConfig) {
            $block = $this->blockFactory->create();

            // Skip creating if block already exists
            if ($block->load($blockConfig['identifier'], 'identifier')->getId()) {
                continue;
            }

            $block->setData($blockConfig);
            $this->blockRepository->save($block);
        }
        $this->moduleDataSetup->endSetup();
    }

    /**
     * Get CMS Blocks Content
     *
     * @return array
     */
    private function getContent(): array
    {
        return [
            [
                'identifier' => 'product_usp',
                'title' => '[Product] USPs',
                'stores' => [0],
                'is_active' => 1,
                'content' => '<ul>
<li><strong>Checkout</strong> as guest</li>
<li><strong>Free</strong> returns</li>
<li><strong>30 days</strong> return policy</li>
</ul>'
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
