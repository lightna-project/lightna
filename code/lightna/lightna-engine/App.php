<?php

declare(strict_types=1);

namespace Lightna\Engine;

use JetBrains\PhpStorm\NoReturn;
use Lightna\Engine\App\Context;
use Lightna\Engine\App\Exception\LightnaException;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Response;
use Lightna\Engine\App\Router;
use Lightna\Engine\App\Router\Action\ActionInterface;
use Lightna\Engine\App\Router\BypassedException;
use Lightna\Engine\App\Router\NoRouteException;
use Lightna\Engine\App\Router\RedirectedException;
use Throwable;

class App extends ObjectA
{
    /**
     * Dependencies section
     */
    protected Router $router;
    protected Context $context;
    protected Response $response;
    /** @AppConfig(router/action) */
    protected array $actions;

    /**
     * Internal properties section
     */
    protected ?array $action;
    protected bool $isNoRoute = false;
    protected bool $isRedirect = false;
    protected array $noRouteAction;
    protected array $defaultNoRouteAction = [
        'name' => 'page',
        'params' => ['type' => 'no-route'],
    ];

    public function run(): void
    {
        $this->startOutput();
        try {
            try {
                $this->action = $this->router->process();
            } catch (NoRouteException) {
                $this->isNoRoute = true;
            } catch (RedirectedException) {
                $this->isRedirect = true;
            } catch (BypassedException) {
                return;
            }

            $this->process();
        } catch (Throwable $e) {
            $this->cleanOutput();
            $this->renderError500($e);
        }

        $this->finishOutput();
    }

    protected function createAction(): ActionInterface
    {
        if (!$className = ($this->actions[$this->action['name']] ?? null)) {
            throw new LightnaException('Router action class for "' . $this->action['name'] . '" not defined');
        }

        return getobj($className, $this->action['params']);
    }

    protected function process(): void
    {
        if ($this->isNoRoute) {
            $this->processNoRoute();
        } elseif ($this->isRedirect) {
            $this->processRedirect();
        } else {
            $this->processAction();
        }
    }

    /** @noinspection PhpUnused */
    protected function defineNoRouteAction(): void
    {
        $this->noRouteAction ??= $this->defaultNoRouteAction;
    }

    protected function processNoRoute(): void
    {
        $this->response->setStatus(404);
        $this->sendHeaders();
        $this->renderNoRoute();
    }

    protected function processRedirect(): void
    {
        $this->sendHeaders();
    }

    protected function processAction(): void
    {
        $this->createAction()->process();
    }

    protected function sendHeaders(): void
    {
        $this->response->sendHeaders();
    }

    protected function renderNoRoute(): void
    {
        $this->action = $this->noRouteAction;

        $this->createAction()->process();
    }

    protected function startOutput(): void
    {
        !IS_PROGRESSIVE_RENDERING && ob_start();
    }

    protected function cleanOutput(): void
    {
        !IS_PROGRESSIVE_RENDERING && ob_end_clean();
    }

    protected function finishOutput(): void
    {
        !IS_PROGRESSIVE_RENDERING && ob_end_flush();
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
