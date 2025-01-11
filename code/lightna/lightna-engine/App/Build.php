<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

class Build extends Opcache
{
    protected string $dir = BUILD_DIR;
    protected array $validateConfigOverrides = [
        'config/frontend' => 1,
        'config/backend' => 1,
        'tailwind/config' => 1,
        'webpack/config' => 1,
    ];

    public function getAppConfig(string $area = null): array
    {
        if (Bootstrap::getCompilerMode() === 'none') {
            return Bootstrap::getConfig();
        } else {
            return merge(
                $this->load('config/' . ($area ?? LIGHTNA_AREA)),
                Bootstrap::getConfig(),
            );
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
            || !is_file($prevFile = LIGHTNA_ENTRY . Bootstrap::getConfig()['compiler_dir'] . "/build/$name.php")
        ) {
            return;
        }

        if (
            !$this->isConfigOverrideAccepted($name)
            && ($overrides = $this->searchConfigOverrides(require $prevFile, $data))
        ) {
            echo $this->getConfigOverrideErrorMessage($name, $overrides) . "\n";
            exit(1);
        }
    }

    protected function isConfigOverrideAccepted(string $name): bool
    {
        return in_array($name, $this->getAcceptedConfigOverrides());
    }

    protected function getAcceptedConfigOverrides(): array
    {
        $arg = cli_get_option('accept-config-override');
        if (!is_string($arg)) {
            return [];
        }

        return explode(',', $arg);
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
            $msg .= $t . $column('Current Value: ') . $override['curr'] . "\n";
        }

        $toAccept = implode(',', merge($this->getAcceptedConfigOverrides(), [$name]));

        $exp = "\n{$t}This message is shown only in DEV MODE and is intended to prevent accidental configuration overrides due to copy-pasting.\n";
        $exp .= "{$t}If these changes are intentional, accept them by adding the following argument:\n";
        $exp .= "{$t}    ./cli build.compile --direct --accept-config-override=$toAccept\n";
        $exp .= "{$t}In case of composer script:\n";
        $exp .= "{$t}    composer lightna.build -- --accept-config-override=$toAccept\n";
        $exp = cli_warning($exp);

        return $msg . $exp;
    }
}
