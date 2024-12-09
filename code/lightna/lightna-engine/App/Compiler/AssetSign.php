<?php

declare(strict_types=1);

namespace Lightna\Engine\App\Compiler;

use Lightna\Engine\Data\Url;

class AssetSign extends CompilerA
{
    protected string $assetDir;
    /** @AppConfig(asset_base) */
    protected string $assetBase;
    protected array $hashes;
    protected Url $url;

    public function run(): void
    {
        $this->generateHashes();
        $this->applyHashesToCss();

        // Update hashes for final CSS so that it's same and correct in different cases
        $this->generateHashes();
    }

    /** @noinspection PhpUnused */
    protected function defineAssetDir(): void
    {
        $this->assetDir = $this->compiler->getAssetDir();
    }

    protected function generateHashes(): void
    {
        $this->hashes = [];
        foreach ($this->getAllAssets() as $file) {
            $this->hashes[$file] = $this->getAssetHash($file);
        }

        $this->build->save('asset/hashes', $this->hashes);
    }

    protected function applyHashesToCss(): void
    {
        foreach ($this->getCssAssets() as $file) {
            $fileName = $this->assetDir . '/' . $file;
            $content = file_get_contents($fileName);
            file_put_contents($fileName, $this->applyHashesToCssContent($content));
        }
    }

    protected function applyHashesToCssContent(string $content): string
    {
        return preg_replace_callback(
            '~url\s*[(]([^)]+)[)]~ism',
            function ($matches) {
                $url = $matches[1];
                $quoted = in_array($url[0], ['\'', '"']);
                $quote = $url[0];
                if ($quoted) {
                    $url = trim($url, $quote);
                }

                if (str_starts_with($url, 'http')) {
                    return $matches[0];
                }

                $url = $this->url->asset($url, false);
                $url = $this->makeUrlRelated($url);

                if ($quoted) {
                    $url = $quote . $url . $quote;
                }

                return 'url(' . $url . ')';
            },
            $content,
        );
    }

    protected function getAllAssets(): array
    {
        return rscan($this->assetDir, '~.*~', false);
    }

    protected function getCssAssets(): array
    {
        return rscan($this->assetDir, '~[.]css$~', false);
    }

    protected function getAssetHash(string $file): string
    {
        return substr(sha1_file($this->assetDir . '/' . $file), 0, 6);
    }

    protected function makeUrlRelated(string $url): string
    {
        return preg_replace('~^' . preg_quote($this->assetBase) . '~', '../../', $url);
    }
}
