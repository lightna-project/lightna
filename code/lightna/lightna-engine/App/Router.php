<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Exception;
use Lightna\Engine\App\Entity\Route;
use Lightna\Engine\App\Router\BypassedException;
use Lightna\Engine\App\Router\NoRouteException;
use Lightna\Engine\App\Router\RedirectedException;
use Lightna\Engine\Data\Request;
use const FILTER_SANITIZE_URL;

class Router extends ObjectA
{
    /** @AppConfig(router/bypass) */
    protected ?array $bypass;
    /** @AppConfig(router/route) */
    protected array $routes;
    protected Route $route;
    protected Request $request;

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
            throw new Exception('Unknown router action "' . $route['action'] . '"');
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
            throw new Exception('Unknown rule for router.bypass.rule.no_route = "' . $rule . '"');
        }
    }

    protected function canBypass(): bool
    {
        if(!$this->bypass['file']) {
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
    protected function redirect(int $type, string $to): void
    {
        if (!preg_match('~^https?://~', $to)) {
            // Add slash to relative URL
            $to = $to[0] !== '/' ? '/' . $to : $to;
        }

        header('Location: ' . filter_var($to, FILTER_SANITIZE_URL), true, $type);

        throw new RedirectedException();
    }
}
