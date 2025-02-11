<?php

declare(strict_types=1);

use Lightna\Engine\App\Bootstrap;
use Lightna\Engine\App\Console\Compile;

function cli_help(): void
{
    $commands = array_keys(cli_get_all_commands());
    echo "\n Available commands:\n\n    " . implode("\n    ", $commands) . "\n\n";
}

function cli_get_all_commands(): array
{
    $edition = $_SERVER['LIGHTNA_EDITION'] ?? 'main';
    $config = require LIGHTNA_ENTRY . "edition/$edition/config.php";
    $compilerDir = $config['compiler_dir'];
    $commands = [
        'build.compile' => true,
        'build.validate' => true,
        'build.apply' => true,
    ];
    $buildConfigFile = LIGHTNA_ENTRY . $compilerDir . "/$edition/build/config/backend.php";

    if (file_exists($buildConfigFile)) {
        $buildConfig = require $buildConfigFile;
        $commands = array_merge($commands, $buildConfig['cli']['command'] ?? []);
    }

    $commands = array_flat($commands);
    ksort($commands);

    return $commands;
}

function cli_init_compiler_mode(): void
{
    require __DIR__ . '/../Bootstrap.php';

    Bootstrap::setCompilerMode(
        cli_get_option('direct') ? 'direct' : 'default',
    );
}

function cli_init_compiler(): void
{
    $requires = [
        'ObjectManagerIgnore',
        'ArrayDirectives',
        'ObjectA',
        'Opcache',
        'Build',
        'Console/CommandA',
        'Console/Compile',
        'Compiler',
        'Compiler/CompilerA',
        'Compiler/ClassMap',
        'lib/common',
    ];

    foreach ($requires as $require) {
        require __DIR__ . '/../' . $require . '.php';
    }
}

function cli_run_compiler(string $command): void
{
    cli_init_compiler();
    if ($command === 'build.compile') {
        (new Compile())->run();
    } elseif ($command === 'build.validate') {
        (new Compile())->validate();
    } elseif ($command === 'build.apply') {
        // Deny "direct" mode for apply command
        Bootstrap::setCompilerMode('default');
        (new Compile())->apply();
    }
}

function file_copy(string $from, string $to): bool
{
    $from = $from[0] === '/' ? $from : LIGHTNA_ENTRY . $from;
    $to = $to[0] === '/' ? $to : LIGHTNA_ENTRY . $to;
    file_mkdir($to);

    return copy($from, $to);
}

function file_put(string $file, string $content): bool|int
{
    file_mkdir($file);
    return file_put_contents($file, $content);
}

function file_mkdir(string $file): void
{
    $dir = dirname($file);
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

function cli_warning(string $text): string
{
    return "\e[33m$text\033[0m";
}

function cli_error(string $text): string
{
    return "\e[31m$text\e[0m";
}

function array_flat(array $array, string $separator = '.'): array
{
    $flat = [];
    foreach ($array as $k => $v) {
        if (is_array($v)) {
            foreach (array_flat($v, $separator) as $vk => $vv) {
                $flat[$k . $separator . $vk] = $vv;
            }
        } else {
            $flat[$k] = $v;
        }
    }

    return $flat;
}

function cli_get_option(string $option): bool|string
{
    global $argv;

    $arg = false;
    foreach ($argv as $v) {
        if (preg_match('/^--' . preg_quote($option) . '/', $v)) $arg = $v;
    }
    if ($arg === false) return false;
    $parts = explode('=', $arg);

    return count($parts) === 1 ? true : $parts[1];
}
