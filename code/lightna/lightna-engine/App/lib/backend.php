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

function array_filter_recursive(array $array, ?callable $cb): array
{
    foreach ($array as $k => $v) {
        if (is_array($v)) {
            $array[$k] = array_filter_recursive($v, $cb);
        } else {
            if (!$cb($k, $v)) {
                unset($array[$k]);
            }
        }
    }

    return $array;
}

function array_expand_keys(array $data, string $sep): array
{
    $expanded = [];
    foreach ($data as $k => $v) {
        $v = is_array($v) ? array_expand_keys($v, $sep) : $v;
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
