<?php

declare(strict_types=1);

namespace Lightna\Engine;

use JetBrains\PhpStorm\NoReturn;
use Lightna\Engine\App\Context;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Router;
use Lightna\Engine\App\Router\BypassedException;
use Lightna\Engine\App\Router\NoRouteException;
use Lightna\Engine\App\Router\RedirectedException;
use Throwable;

class App extends ObjectA
{
    protected Router $router;
    protected Context $context;
    protected ?array $action;
    protected bool $noRoute = false;
    protected bool $redirected = false;
    /** @AppConfig(router/action) */
    protected array $actions;

    public function run(): void
    {
        $this->startRendering();
        try {
            try {
                $this->action = $this->router->process();
            } catch (NoRouteException) {
                $this->noRoute = true;
            } catch (BypassedException|RedirectedException) {
                return;
            }

            $this->process();
        } catch (Throwable $e) {
            $this->cleanRendering();
            $this->renderError500($e);
        }

        $this->finishRendering();
    }

    protected function startRendering(): void
    {
        !IS_PROGRESSIVE_RENDERING && ob_start();
    }

    protected function cleanRendering(): void
    {
        !IS_PROGRESSIVE_RENDERING && ob_end_clean();
    }

    protected function finishRendering(): void
    {
        !IS_PROGRESSIVE_RENDERING && ob_end_flush();
    }

    protected function createAction(): object
    {
        if (!$className = ($this->actions[$this->action['name']] ?? null)) {
            throw new \Exception('Router action class for "' . $this->action['name'] . '" not defined');
        }

        return getobj($className, $this->action['params']);
    }

    protected function process(): void
    {
        $this->sendHeaders();

        if ($this->noRoute) {
            $this->processNoRoute();
        } elseif (isset($this->action)) {
            $this->processAction();
        }
    }

    protected function sendHeaders(): void
    {
        $cspHeaderProvider = new \Lightna\Engine\App\HeaderProvider\ContentSecurityPolicy();
        $cspHeaderProvider->addPolicy('default-src', "'self'");
        $cspHeaderProvider->addPolicy('script-src', "'self'");
        $cspHeaderProvider->addPolicy('style-src', "'self'");
        $cspHeaderProvider->addPolicy('img-src', "'self'");
        $cspHeaderProvider->addPolicy('font-src', "'self'");
        $cspHeaderProvider->addPolicy('connect-src', "'self'");
        $cspHeaderProvider->addPolicy('frame-src', "'self'");
        $cspHeaderProvider->addPolicy('form-action', "'self'");
        $cspHeaderProvider->addPolicy('base-uri', "'self'");
        $cspHeaderProvider->addPolicy('frame-ancestors', "'self'");
        $cspHeaderProvider->addPolicy('media-src', "'self'");
        $cspHeaderProvider->addPolicy('manifest-src', "'self'");
        $cspHeaderProvider->addPolicy('child-src', "'self'");
        $cspHeaderProvider->addPolicy('object-src', "'self'");
        $cspHeaderProvider->addNoncePolicy();

        $headerProviders = [
            $cspHeaderProvider,
            new \Lightna\Engine\App\HeaderProvider\CacheControl(),
            new \Lightna\Engine\App\HeaderProvider\XFrameOptions(),
            new \Lightna\Engine\App\HeaderProvider\XContentTypeOptions(),
            new \Lightna\Engine\App\HeaderProvider\XssProtection(),
        ];
        $headerManager = new \Lightna\Engine\App\HeaderManager($headerProviders);
        $headerManager->sendHeaders();
    }

    protected function processAction(): void
    {
        $this->createAction()->process();
    }

    protected function processNoRoute(): void
    {
        http_response_code(404);
        $this->renderNoRoute();
    }

    protected function renderNoRoute(): void
    {
        $this->action = [
            'name' => 'page',
            'params' => ['type' => 'no-route'],
        ];

        $this->processAction();
    }

    #[NoReturn]
    protected function renderError500(Throwable $e): void
    {
        if (IS_DEV_MODE) {
            $this->renderError500Dev($e);
        }

        error500('Application internal error', $e);
    }

    #[NoReturn]
    protected function renderError500Dev(Throwable $e): void
    {
        http_response_code(500);
        echo "<pre>\n$e\n</pre>";
        exit(1);
    }
}
