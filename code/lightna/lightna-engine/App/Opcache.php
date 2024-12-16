<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use Throwable;

abstract class Opcache extends ObjectA
{
    protected string $dir;

    public function getDir(): string
    {
        return $this->dir;
    }

    public function load(string $name): mixed
    {
        return require $this->dir . $this->getFileName($name);
    }

    public function loadRevalidated(string $name): mixed
    {
        return opcache_load_revalidated_soft($this->dir . $this->getFileName($name));
    }

    protected function getFileName(string $name): string
    {
        return $name . '.php';
    }

    public function save(string $name, mixed $data): void
    {
        $this->putFile(
            $this->getFileName($name),
            "<?php\nreturn " . var_export($data, true) . ';',
        );
    }

    public function delete(string $name): void
    {
        is_file($file = $this->dir . $this->getFileName($name)) && unlink($file);
    }

    public function putFile(string $file, string $content): void
    {
        try {
            file_put($this->dir . $file, $content);
        } catch (Throwable $e) {
            echo "\nERROR: Failed to write file: " . $this->dir . $file . "\n";
            throw $e;
        }
    }
}
