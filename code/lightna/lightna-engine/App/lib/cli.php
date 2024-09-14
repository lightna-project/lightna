<?php

declare(strict_types=1);

function cli_get_all_commands(string $codeDir): array
{
    $commands = ['compile' => []];
    $compiledConfigFile = LIGHTNA_ENTRY . $codeDir . '/build/config/backend.php';
    if (file_exists($compiledConfigFile)) {
        $compiledConfig = require $compiledConfigFile;
        $moreCommands = array_flip(array_keys($compiledConfig['cli']['command'] ?? []));
        $commands = array_merge($commands, $moreCommands);
        ksort($commands);
    }

    return $commands;
}

function cli_init_compiler_without_autoload(): void
{
    $requires = [
        'ObjectManagerIgnore',
        'ArrayDirectives',
        'ObjectA',
        'Opcache',
        'Opcache/Compiled',
        'Console/CommandA',
        'Console/Compile',
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
