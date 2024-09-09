<?php

declare(strict_types=1);

namespace Lightna\Session\App\Session;

use Exception;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\Data\Request;

class Cookie extends ObjectA
{
    protected array $config;
    protected Request $request;

    protected function init(array $config): void
    {
        $this->config = $config;
    }

    public function prolong(): void
    {
        $cName = $this->config['name'];
        if (!isset($_COOKIE[$cName])) {
            return;
        }

        setcookie(
            $cName,
            $_COOKIE[$cName],
            $this->getOptions(),
        );
    }

    protected function getOptions(): array
    {
        $lifetime = (int)($this->config['lifetime'] ?? 0);

        return [
            'expires' => $lifetime > 0 ? time() + $lifetime : 3600,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => $this->getIsSecure(),
            'httponly' => true,
            'samesite' => 'Lax',
        ];
    }

    protected function getIsSecure(): bool
    {
        $secure = $this->request->isSecure;
        if (!$secure && !IS_DEV_MODE) {
            throw new Exception('Can\'t set unsecure session cookie');
        }

        return $secure;
    }
}
