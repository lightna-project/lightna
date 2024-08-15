<?php

declare(strict_types=1);

use Lightna\Engine\App;

function error500(string $title, Throwable $exception): void
{
    http_response_code(500);
    error_log(($logId = uniqid()) . ' ' . $exception);
    require __DIR__ . '/template/error/500.phtml';
    exit(1);
}

try {

    $config = require LIGHTNA_ENTRY . 'config.php';
    require LIGHTNA_ENTRY . $config['src_dir'] . '/App/boot.php';

    $app = getobj(App::class);
    $app->run();

} catch (Throwable $e) {
    error500('Initialization error', $e);
}
