<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Exception;
use Lightna\Engine\App\Entity\Route;
use Lightna\Engine\App\Exception\LightnaException;
use Lightna\Engine\App\Router\BypassedException;
use Lightna\Engine\App\Router\NoRouteException;
use Lightna\Engine\App\Router\RedirectedException;
use Lightna\Engine\Data\Request;

class Router extends ObjectA
{
    /** @AppConfig(router/bypass) */
    protected ?array $bypass;
    /** @AppConfig(router/route) */
    protected array $routes;
    protected Route $route;
    protected Request $request;
    protected Response $response;

    /**
     * @throws NoRouteException
     * @throws BypassedException
     * @throws RedirectedException
     */
    public function process(): array
    {
        $this->processBypass();

        return $this->resolveAction();
    }

    /**
     * @throws BypassedException
     * @throws NoRouteException
     */
    protected function resolveAction(): array
    {
        if (!$route = $this->resolveRoute()) {
            $this->processNoRouteRule();
        }

        return $this->resolveRouteAction($route);
    }

    protected function resolveRoute(): ?array
    {
        return $this->resolveHardRoute() ?? $this->resolveSoftRoute();
    }

    /**
     * @throws RedirectedException
     */
    protected function resolveRouteAction(array $route): array
    {
        $action = null;
        if ($route['action'] == Route::ACTION_302) {
            $this->redirect(302, $route['params'][0]);
        } elseif ($route['action'] == Route::ACTION_301) {
            $this->redirect(301, $route['params'][0]);
        } elseif ($route['action'] == Route::ACTION_CUSTOM) {
            $action = ['name' => $route['params'][0], 'params' => $route['params'][1]];
        } else {
            throw new LightnaException('Unknown router action "' . $route['action'] . '"');
        }

        return $action;
    }

    protected function resolveHardRoute(): ?array
    {
        if (isset($this->routes[$this->request->uriPath])) {
            return [
                'action' => Route::ACTION_CUSTOM,
                'params' => [$this->routes[$this->request->uriPath], []],
            ];
        }

        return null;
    }

    protected function resolveSoftRoute(): ?array
    {
        return $this->route->get($this->request->uriPath);
    }

    protected function processBypass(): void
    {
        $this->canBypass() && $this->bypass();
    }

    /**
     * @throws BypassedException
     * @throws NoRouteException
     * @throws Exception
     */
    protected function processNoRouteRule(): void
    {
        $rule = $this->bypass['rule']['no_route'] ?? '';
        if ($rule == 404) {
            throw new NoRouteException();
        } elseif ($rule === 'bypass') {
            $this->bypass();
        } else {
            throw new LightnaException('Unknown rule for router.bypass.rule.no_route = "' . $rule . '"');
        }
    }

    protected function canBypass(): bool
    {
        if (!$this->bypass['file']) {
            return false;
        }

        $bypass =
            ($bypassUrls = $this->bypass['rule']['url_starts_with'])
            && is_array($bypassUrls) && count($bypassUrls)
            && preg_match('~^(' . implode('|', $bypassUrls) . ')~', $this->request->uriPath);

        if (!$bypass) {
            $cookieName = $this->bypass['cookie']['name'] ?? null;
            $cookieValue = $this->bypass['cookie']['value'] ?? null;

            if ($cookieName && $cookieValue) {
                $bypass = ($_COOKIE[$cookieName] ?? null) === $cookieValue;
            }
        }

        return $bypass;
    }

    /**
     * @throws BypassedException
     */
    protected function bypass(): void
    {
        Bootstrap::unregister();
        require LIGHTNA_ENTRY . $this->bypass['file'];

        throw new BypassedException();
    }

    /**
     * @throws RedirectedException
     */
    protected function redirect(int $code, string $url): void
    {
        $this->response->redirect($url, $code);

        throw new RedirectedException();
    }
}
