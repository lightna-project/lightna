<?php

declare(strict_types=1);

namespace Lightna\Engine\Data;

use Exception;
use Lightna\Engine\App\Opcache\Compiled;

class Url extends DataA
{
    protected Compiled $compiled;
    protected array $assetHashes;
    /** @AppConfig(asset_base) */
    protected string $assetBase;

    protected function defineAssetHashes(): void
    {
        $this->assetHashes = $this->compiled->load('asset/hashes');
    }

    public function asset(string $url, bool $escape = true): string
    {
        $url = $this->parseAssetUrl($url);
        if (!isset($this->assetHashes[$url->path])) {
            throw new Exception('Incorrect asset URL "' . $url->orig . '", make sure it contains path to the module and is relative"');
        }

        $url->params['ch'] = $this->assetHashes[$url->path];
        $assetUrl = $this->assetBase . $this->buildAssetUrl($url);

        return $escape ? escape($assetUrl) : $assetUrl;
    }

    protected function parseAssetUrl(string $assetUrl): object
    {
        $url = ltrim($assetUrl, '/.');
        $parts = explode('?', $url);
        parse_str($parts[1] ?? '', $params);

        return (object)[
            'path' => $parts[0],
            'params' => $params,
            'orig' => $assetUrl,
        ];
    }

    protected function buildAssetUrl(object $url): string
    {
        $result = $url->path;
        if ($url->params) {
            $result .= '?' . http_build_query($url->params);
        }

        return $result;
    }
}
