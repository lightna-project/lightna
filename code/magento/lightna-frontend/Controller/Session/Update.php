<?php

declare(strict_types=1);

namespace Lightna\Frontend\Controller\Session;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;

class Update implements HttpPostActionInterface
{
    public function __construct(
        protected ResultFactory $resultFactory,
    ) {
    }

    public function execute()
    {
        // No need to update Lightna session, it will be triggered in Lightna\Frontend\Plugin\FrontController

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([]);
    }
}
