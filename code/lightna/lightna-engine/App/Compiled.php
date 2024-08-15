<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

class Compiled extends ObjectA
{
    public function load(string $name): mixed
    {
        return require LIGHTNA_CODE . $name . '.php';
    }

    public function loadAppConfig(string $scope = null): mixed
    {
        return require LIGHTNA_CODE . 'config/' . ($scope ?? LIGHTNA_AREA) . '.php';
    }

    public function save(string $name, mixed $data): void
    {
        $this->putFile($name . '.php', "<?php\nreturn " . var_export($data, true) . ';');
    }

    public function delete(string $name): void
    {
        is_file($file = LIGHTNA_CODE . $name . '.php') && unlink($file);
    }

    public function getFile(string $file): string
    {
        return file_get_contents(LIGHTNA_CODE . $file);
    }

    public function putFile(string $file, string $content): void
    {
        file_put(LIGHTNA_CODE . $file, $content);
    }
}
