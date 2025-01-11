<?php

declare(strict_types=1);

namespace Lightna\Frontend\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Lightna_Frontend::index';

    public function __construct(
        Action\Context $context,
        protected PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): Page
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Lightna_Frontend::index');
        $resultPage->getConfig()->getTitle()->prepend(__('Lightna Index'));

        return $resultPage;
    }
}
