<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

class Build extends Opcache
{
    protected string $dir;
    protected int $version;
    protected array $validateConfigOverrides = [
        'config/frontend' => 1,
        'config/backend' => 1,
    ];

    public function init(array $data = []): void
    {
        $this->dir = Bootstrap::getBuildDir();
        parent::init($data);
    }

    public function getAppConfig(string $area = null): array
    {
        if (Bootstrap::getCompilerMode() === 'none') {
            return Bootstrap::getConfig();
        } else {
            return Bootstrap::getUnappliedAreaConfig($area ?? LIGHTNA_AREA);
        }
    }

    #[\Override]
    public function save(string $name, mixed $data): void
    {
        $this->validateConfigOverrides($name, $data);

        parent::save($name, $data);
    }

    protected function validateConfigOverrides(string $name, mixed $data): void
    {
        if (
            !IS_DEV_MODE
            || PHP_SAPI !== 'cli'
            || !isset($this->validateConfigOverrides[$name])
            || !is_file($prevFile = dirname(Bootstrap::getBuildDir()) . "/build/$name.php")
        ) {
            return;
        }

        if ($overrides = $this->searchConfigOverrides(require $prevFile, $data)) {
            echo $this->getConfigOverrideErrorMessage($name, $overrides) . "\n";
            $this->acceptConfigOverrides();
        }
    }

    protected function searchConfigOverrides(array $prev, array $curr): array
    {
        $prev = array_flat($prev);
        $curr = array_flat($curr);

        $overrides = [];
        foreach ($prev as $key => $value) {
            if (
                // Ignore numeric keys
                preg_match('~\.[0-9]+(\.|$)~', $key)
                // Ignore removed values
                || !isset($curr[$key])
            ) {
                continue;
            }

            if ($value !== $curr[$key]) {
                $overrides[] = [
                    'key' => $key,
                    'prev' => $value,
                    'curr' => $curr[$key] ?? null,
                ];
            }
        }

        return $overrides;
    }

    protected function getConfigOverrideErrorMessage(string $name, array $overrides): string
    {
        $t = "     ";
        $msg = "\n\n$t" . cli_warning("Config overrides detected in \"$name\":") . "\n";
        $column = fn(string $name) => str_pad($name, 20, ' ', STR_PAD_LEFT);

        foreach ($overrides as $override) {
            $msg .= "\n$t" . $column('Config Key: ') . $override['key'] . "\n";
            $msg .= $t . $column('Previous Value: ') . $override['prev'] . "\n";
            $msg .= $t . $column('New Value: ') . $override['curr'] . "\n";
        }

        $msg .= cli_warning("\n{$t}This message is shown only in DEV MODE and is intended to prevent accidental configuration overrides due to copy-pasting.\n");

        return $msg;
    }

    protected function acceptConfigOverrides(): void
    {
        echo "Press A to accept or CTRL+C to abort: ";
        $answer = trim(fgets(STDIN));
        if ($answer !== 'a' && $answer !== 'A') {
            $this->acceptConfigOverrides();
        }
    }

    public function addValidateConfigOverrides(string $name): void
    {
        $this->validateConfigOverrides[$name] = 1;
    }

    public function getVersion(): int
    {
        if (!isset($this->version)) {
            $this->version = $this->load('version');
        }

        return $this->version;
    }
}
