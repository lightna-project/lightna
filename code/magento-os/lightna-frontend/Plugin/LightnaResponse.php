<?php

declare(strict_types=1);

namespace Lightna\Frontend\Plugin;

use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\View\Element\Message\InterpretationMediator;

class LightnaResponse
{
    public function __construct(
        protected ResultJsonFactory $resultJsonFactory,
        protected MessageManager $messageManager,
        protected InterpretationMediator $interpretationMediator,
    ) {
    }

    public function afterDispatch(
        FrontControllerInterface $subject,
        ResponseInterface|ResultInterface $result,
        RequestHttp $request,
    ): ResponseInterface|ResultInterface {
        if ($request->getHeader('X-Request-With') !== 'Lightna') {
            return $result;
        }

        if (!$result instanceof ResultJson) {
            $data = [];
            if ($messages = $this->convertMessages()) {
                $data['messagesHtml'] = templateHtml('page/messages.phtml', compact('messages'));
            }
            $result = $this->resultJsonFactory->create()->setData($data);
        }

        return $result;
    }

    protected function convertMessages(): array
    {
        $messages = $this->messageManager->getMessages(true);
        if (!$messages->getCount()) {
            return [];
        }

        $result = [];
        foreach ($messages->getItems() as $message) {
            $result[] = [
                'type' => $message->getType(),
                'text' => $this->interpretationMediator->interpret($message),
            ];
        }

        return $result;
    }
}
