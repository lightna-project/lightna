<?php

declare(strict_types=1);

namespace Lightna\Engine;

use Lightna\Engine\App\NotFoundException;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Router;
use Lightna\Engine\App\Router\PassedException as RouterPassedException;
use Lightna\Engine\App\Context;
use Throwable;

class App extends ObjectA
{
    protected Router $router;
    protected Context $context;
    protected ?array $action;
    protected bool $noRoute = false;
    /** @AppConfig(router/action) */
    protected array $actions;

    public function run(): void
    {
        $this->startRendering();
        try {
            try {
                $this->action = $this->router->process();
            } catch (NotFoundException) {
                $this->noRoute = true;
            } catch (RouterPassedException) {
                // Router bypassed, no error handling required
            }

            if (isset($this->action) || $this->noRoute) {
                $this->process();
            }
        } catch (Throwable $e) {
            $this->cleanRendering();
            $this->renderError500($e);
        }

        $this->finishRendering();
    }

    protected function startRendering(): void
    {
        if (!IS_PROGRESSIVE_RENDERING) {
            ob_start();
        }
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
            http_response_code(404);
            $this->renderNoRoute();
        } else {
            $this->runAction();
        }
    }

    protected function sendHeaders(): void
    {
        header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate, private');
    }

    protected function runAction(): void
    {
        $this->createAction()->process();
    }

    protected function renderNoRoute(): void
    {
        $this->action = [
            'name' => 'page',
            'params' => ['type' => 'no-route'],
        ];

        $this->runAction();
    }

    protected function renderError500(Throwable $e): void
    {
        error500('Application internal error', $e);
    }
}
