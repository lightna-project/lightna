<?php

declare(strict_types=1);

function cli_get_all_commands(string $codeDir): array
{
    $commands = [
        'build.compile' => true,
        'build.apply' => true,
    ];
    $compiledConfigFile = LIGHTNA_ENTRY . $codeDir . '/build/config/backend.php';
    if (file_exists($compiledConfigFile)) {
        $compiledConfig = require $compiledConfigFile;
        $commands = array_merge($commands, $compiledConfig['cli']['command'] ?? []);
    }

    $commands = array_flat($commands);
    ksort($commands);

    return $commands;
}

function cli_init_compiler(): void
{
    $requires = [
        'ObjectManagerIgnore',
        'ArrayDirectives',
        'ObjectA',
        'Opcache',
        'Opcache/Compiled',
        'Console/CommandA',
        'Console/Compile',
        'Bootstrap',
        'Compiler',
        'Compiler/CompilerA',
        'Compiler/ClassMap',
        'lib/common',
    ];

    foreach ($requires as $require) {
        require __DIR__ . '/../' . $require . '.php';
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
