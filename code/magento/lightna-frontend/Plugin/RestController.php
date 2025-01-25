<?php

declare(strict_types=1);

namespace Lightna\Frontend\Plugin;

use Lightna\Frontend\Model\Session\Manager as LightnaSessionManager;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class RestController
{
    public function __construct(
        protected LightnaSessionManager $lightnaSessionManager,
        protected ResourceConnection $resource,
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

        return $result;
    }

    protected function updateLightnaSession(RequestHttp $request): void
    {
        if ($request->getMethod() === RequestHttp::METHOD_POST && $this->sessionExists()) {
            $this->lightnaSessionManager->updateData(true);
        }
    }

    protected function sessionExists(): bool
    {
        return isset($_COOKIE[session_name()]);
    }
}
