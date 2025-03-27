<?php

declare(strict_types=1);

namespace Lightna\Frontend\Plugin;

use Lightna\Frontend\Model\Session\Manager as LightnaSessionManager;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Response\HttpFactory as ResponseHttpFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Element\Message\InterpretationMediator;

class FrontController
{
    public function __construct(
        protected MessageManager $messageManager,
        protected InterpretationMediator $interpretationMediator,
        protected RequestHttp $request,
        protected LightnaSessionManager $lightnaSessionManager,
        protected ResourceConnection $resource,
        protected ResponseHttpFactory $responseHttpFactory,
    ) {
    }

    /** @noinspection PhpUnused */
    public function beforeDispatch(): void
    {
        $GLOBALS['SHARED_PDO_CONNECTION'] = $this->resource->getConnection()->getConnection();
    }

    /** @noinspection PhpUnused */
    public function afterDispatch(
        FrontControllerInterface $subject,
        ResponseInterface|ResultInterface $result,
        RequestHttp $request,
    ): ResponseInterface|ResultInterface {
        $this->updateLightnaSession($request);

        return $this->getLightnaResult($request, $result);
    }

    protected function updateLightnaSession(RequestHttp $request): void
    {
        if ($request->getMethod() === RequestHttp::METHOD_POST) {
            $this->lightnaSessionManager->updateData(true);
        }
    }

    protected function getLightnaResult(
        RequestHttp $request,
        ResponseInterface|ResultInterface $result,
    ): ResponseInterface|ResultInterface {
        if ($request->getHeader('X-Request-With') !== 'Lightna') {
            return $result;
        }

        if (!$result instanceof ResultJson) {
            $data = [];
            if ($messages = $this->prepareMessages()) {
                $data['messagesHtml'] = blockhtml('#messages', compact('messages'));
            }

            // Avoid JsonFactory as it doesn't replace redirect
            $result = $this->responseHttpFactory->create()
                ->setHeader('Content-Type', 'application/json')
                ->setHeader('Cache-Control', 'private')
                ->setBody(json_encode($data));
        }

        return $result;
    }

    protected function prepareMessages(): array
    {
        $messages = $this->messageManager->getMessages(true);
        if (!$messages->getCount()) {
            return [];
        }

        $result = [];
        foreach ($messages->getItems() as $message) {
            if ($message->getType() === MessageInterface::TYPE_SUCCESS && $this->request->get('noSuccessMessages')) {
                continue;
            }
            $result[] = [
                'type' => $message->getType(),
                'text' => $this->interpretationMediator->interpret($message),
            ];
        }

        return $result;
    }
}
