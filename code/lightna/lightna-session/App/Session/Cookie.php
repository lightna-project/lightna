<?php

declare(strict_types=1);

namespace Lightna\Session\App\Session;

use Lightna\Engine\App\Exception\LightnaException;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\Data\Request;

class Cookie extends ObjectA
{
    protected Request $request;

    public function prolong(): void
    {
        if (!isset($_COOKIE[session_name()])) {
            return;
        }

        setcookie(
            session_name(),
            $_COOKIE[session_name()],
            $this->getOptions(),
        );
    }

    protected function getOptions(): array
    {
        $lifetime = session_get_cookie_params()['lifetime'];

        return [
            'expires' => $lifetime > 0 ? time() + $lifetime : $lifetime,
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
            throw new LightnaException('Can\'t set unsecure session cookie');
        }

        return $secure;
    }
}
