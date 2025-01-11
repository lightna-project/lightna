<?php

declare(strict_types=1);

namespace Lightna\Frontend\Controller\Adminhtml\Index;

use Lightna\Engine\App\State\Index as IndexState;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

class InvalidateAll extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Lightna_Frontend::index';

    public function execute(): Redirect
    {
        newobj(IndexState::class)->invalidateAll();

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('lightna/index/index');
    }
}
