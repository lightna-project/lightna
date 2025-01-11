<?php

declare(strict_types=1);

namespace Lightna\Frontend\Setup\Patch\Data;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterfaceFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateFooterLinksCmsBlock implements DataPatchInterface
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
                'identifier' => 'footer_links',
                'title' => '[Footer] Main Links',
                'stores' => [0],
                'is_active' => 1,
                'content' => '<div class="columns">
    <div>
        <div class="title">About US</div>
        <ul>
            <li><a href="#">Our story</a></li>
            <li><a href="#">Jobs</a></li>
            <li><a href="#">Sustainability</a></li>
        </ul>
    </div>
    <div>
        <div class="title">Customer Service</div>
        <ul>
            <li><a href="#">FAQ</a></li>
            <li><a href="#">Orders</a></li>
            <li><a href="#">Delivery</a></li>
            <li><a href="#">Payment</a></li>
            <li><a href="#">Returns</a></li>
        </ul>
    </div>
    <div>
        <div class="title">Page Information</div>
        <ul>
            <li><a href="#">Terms and conditions</a></li>
            <li><a href="#">Privacy policy</a></li>
            <li><a href="#">Cookie policy</a></li>
        </ul>
    </div>
    <div>
        <div class="title">Contact</div>
        <div>
            Send your message to support@example.com
            or call our Customer Service
            +55 (555) 555 55 55
            Mon - Fri: 9.00 – 17.00
        </div>
    </div>
</div>'
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
