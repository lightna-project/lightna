<?php

declare(strict_types=1);

namespace Lightna\Frontend\Controller\Adminhtml\Index;

use Lightna\Engine\App\State\Index as IndexState;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;

class MassInvalidate extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Lightna_Frontend::index';

    public function execute(): Redirect
    {
        $indices = $this->getRequest()->getParam('indices', []);
        newobj(IndexState::class)->invalidate($indices);

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('lightna/index/index');
    }
}
