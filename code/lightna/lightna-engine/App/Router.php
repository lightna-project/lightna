<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Lightna\Engine\App\Entity\Route;
use Lightna\Engine\App\Router\PassedException;
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

    public function process(): ?array
    {
        $this->processBypassBeforeRouting();

        $route = $this->resolveRoute();
        $customAction = null;

        if ($route) {
            $action = $route['action'];
            $params = $route['params'];

            if ($action == Route::ACTION_302) {
                $this->redirect(302, $params[0]);
            } elseif ($action == Route::ACTION_301) {
                $this->redirect(301, $params[0]);
            } elseif ($action == Route::ACTION_CUSTOM) {
                $customAction = ['name' => $params[0], 'params' => $params[1]];
            } else {
                throw new \Exception('Unknown router action "' . $action . '"');
            }
        } else {
            $this->processBypassAfterRouting();

            // If no bypass then 404
            throw new NotFoundException();
        }

        return $customAction;
    }

    protected function processBypassBeforeRouting(): void
    {
        if (!$this->bypass['process_after_routing']) {
            $this->processBypass();
        }
    }

    protected function processBypassAfterRouting(): void
    {
        if ($this->bypass['process_after_routing']) {
            $this->processBypass();
        }
    }

    protected function resolveRoute(): ?array
    {
        return $this->resolveHardRoute() ?? $this->resolveSoftRoute();
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
        $bypass =
            ($fallback = $this->bypass['file'] ?? false)
            && ($bypassUrls = $this->bypass['rules']['url_starts_with'] ?? false)
            && is_array($bypassUrls) && count($bypassUrls)
            && preg_match('~^(' . implode('|', $bypassUrls) . ')~', $this->request->uriPath);

        $bypass =
            $bypass || (
                $this->bypass['cookie']['enabled']
                && ($_COOKIE[$this->bypass['cookie']['name']] ?? null)
            );

        if ($bypass) {
            Bootstrap::unregister();
            require LIGHTNA_ENTRY . $fallback;

            throw new PassedException();
        }
    }

    protected function redirect(int $type, string $to): void
    {
        if (!preg_match('~^https?://~', $to)) {
            // Add slash to relative URL
            $to = $to[0] !== '/' ? '/' . $to : $to;
        }

        header('Location: ' . filter_var($to, FILTER_SANITIZE_URL), true, $type);
    }
}
