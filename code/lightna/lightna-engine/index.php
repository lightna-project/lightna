<?php

declare(strict_types=1);

use JetBrains\PhpStorm\NoReturn;
use Lightna\Engine\App;

$GLOBALS['LIGHTNA_START_TIME'] = microtime(true);

/** @noinspection PhpUnusedParameterInspection */
#[NoReturn]
function error500(string $title, Throwable $exception): void
{
    http_response_code(500);
    error_log(($logId = uniqid()) . ' ' . $exception);
    require __DIR__ . '/template/error/500.phtml';
    exit(1);
}

try {
    require_once __DIR__ . '/App/boot.php';
    (getobj(App::class))->run();

} catch (Throwable $e) {
    error500('Initialization error', $e);
}
