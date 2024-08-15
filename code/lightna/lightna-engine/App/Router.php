<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Lightna\Engine\App\Entity\Route;
use Lightna\Engine\App\Router\PassedException;
use const FILTER_SANITIZE_URL;

class Router extends ObjectA
{
    protected string $urlPath;
    /** @AppConfig(router/bypass) */
    protected ?array $bypass;
    protected Route $route;

    protected function init(): void
    {
        $url = $_SERVER['REQUEST_URI'];
        $qi = strpos($url, '?');

        $this->urlPath = substr($url, 1, $qi !== false ? $qi - 1 : null);
    }

    public function process(): ?array
    {
        $this->processBypassBeforeRouting();

        $route = $this->route->get($this->urlPath);
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

    protected function processBypass(): void
    {
        $bypass =
            ($fallback = $this->bypass['file'] ?? false)
            && ($bypassUrls = $this->bypass['rules']['url_starts_with'] ?? false)
            && is_array($bypassUrls) && count($bypassUrls)
            && preg_match('~^(' . implode('|', $bypassUrls) . ')~', $this->urlPath);

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
