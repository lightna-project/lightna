<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

abstract class Opcache extends ObjectA
{
    protected string $dir;

    public function getDir(): string
    {
        return $this->dir;
    }

    public function load(string $name): mixed
    {
        return require $this->dir . $name . '.php';
    }

    public function save(string $name, mixed $data): void
    {
        $this->putFile($name . '.php', "<?php\nreturn " . var_export($data, true) . ';');
    }

    public function delete(string $name): void
    {
        is_file($file = $this->dir . $name . '.php') && unlink($file);
    }

    public function getFile(string $file): string
    {
        return file_get_contents($this->dir . $file);
    }

    public function putFile(string $file, string $content): void
    {
        file_put($this->dir . $file, $content);
    }

    public function clean(array $tags = []): void
    {
        rcleandir($this->dir);
    }
}
