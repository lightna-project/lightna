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
    /** @AppConfig(router/routes) */
    protected array $routes;
    protected Route $route;
    protected Request $request;

    public function process(): array
    {
        $this->processBypass();

        return $this->resolveAction();
    }

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
        $this->isBypass() && $this->bypass();
    }

    protected function processNoRouteRule(): void
    {
        $rule = $this->bypass['rules']['no_route'] ?? '';
        if ($rule == 404) {
            throw new NoRouteException();
        } elseif ($rule === 'bypass') {
            $this->bypass();
        } else {
            throw new Exception('Unknown rule for router.bypass.rules.no_route = "' . $rule . '"');
        }
    }

    protected function isBypass(): bool
    {
        $bypass =
            ($this->bypass['file'] ?? false)
            && ($bypassUrls = $this->bypass['rules']['url_starts_with'] ?? false)
            && is_array($bypassUrls) && count($bypassUrls)
            && preg_match('~^(' . implode('|', $bypassUrls) . ')~', $this->request->uriPath);

        $bypass =
            $bypass || (
                $this->bypass['cookie']['enabled']
                && ($_COOKIE[$this->bypass['cookie']['name']] ?? null)
            );

        return $bypass;
    }

    protected function bypass(): void
    {
        Bootstrap::unregister();
        require LIGHTNA_ENTRY . $this->bypass['file'];

        throw new BypassedException();
    }

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
