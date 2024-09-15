<?php

declare(strict_types=1);

namespace Lightna\Engine\Data;

use Exception;
use Lightna\Engine\App\Compiled;

class Url extends DataA
{
    protected Compiled $compiled;
    protected array $assetHashes;
    /** @AppConfig(asset_base) */
    protected string $assetBase;

    protected function init(array $data = []): void
    {
        $this->assetHashes = $this->compiled->load('asset/hashes');
    }

    public function asset(string $url, bool $escape = true): string
    {
        $url = $this->parseAssetUrl($url);
        if (!isset($this->assetHashes[$url->key])) {
            throw new Exception('Incorrect asset URL "' . $url->orig . '", make sure it contains path to the module and is relative"');
        }
        $assetUrl = $this->assetBase . $url->key . '?';
        $assetUrl .= $url->params !== '' ? $url->params . '&' : '';
        $assetUrl .= 'ch=' . $this->assetHashes[$url->key];

        return $escape ? escape($assetUrl) : $assetUrl;
    }

    protected function parseAssetUrl(string $assetUrl): object
    {
        $url = ltrim($assetUrl, '/.');
        $parts = explode('?', $url);

        return (object)[
            'key' => $parts[0],
            'params' => $parts[1] ?? '',
            'orig' => $assetUrl,
        ];
    }
}
