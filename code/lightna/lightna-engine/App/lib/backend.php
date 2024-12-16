<?php

declare(strict_types=1);

function rscan(string $folder, string $rx, bool $returnFullPath = true): array
{
    $files = new RegexIterator(
        new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folder)
        ),
        $rx,
        RegexIterator::MATCH
    );

    $list = [];
    foreach ($files as $file) {
        if (is_file($file->getPathname())) {
            /** @var SplFileInfo $file */
            $list[] = realpath($file->getPathname());
        }
    }

    if (!$returnFullPath) {
        $folderRp = realpath($folder);
        foreach ($list as $i => $file) {
            $list[$i] = preg_replace(
                '~^' . preg_quote($folderRp . '/', '~') . '~',
                '',
                realpath($file)
            );
        }
    }

    return $list;
}

function rcleandir(string $dir, $isNestedCall = false): void
{
    if (!is_dir($dir)) {
        return;
    }

    $files = scandir($dir);
    foreach ($files as $file) {
        if (in_array($file, ['.', '..'])) {
            continue;
        }
        $path = $dir . '/' . $file;
        is_dir($path) ? rcleandir($path, true) : unlink($path);
    }

    if ($isNestedCall) {
        rmdir($dir);
    }
}

function array_expand_keys(array $data, string $sep, array $ignore = [], string $path = ''): array
{
    $expanded = [];
    foreach ($data as $k => $v) {
        $p = $path . '/' . $k;
        if (isset($ignore[$p])) {
            $expanded[$k] = $v;
            continue;
        }

        $v = is_array($v) ? array_expand_keys($v, $sep, $ignore, $p) : $v;
        $k = (string)$k;
        $parts = explode($sep, trim($k, $sep));
        if (count($parts) > 1) {
            $dest = &$expanded;
            foreach ($parts as $part) {
                $dest = &$dest[$part];
            }
            $dest = $v;
        } else {
            $expanded[$k] = $v;
        }
    }

    return $expanded;
}

function filter_extra_spaces(string $str): string
{
    return trim(preg_replace('~\s+~', ' ', $str));
}
