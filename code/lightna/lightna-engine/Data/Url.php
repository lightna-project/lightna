<?php

declare(strict_types=1);

namespace Lightna\Engine\Data;

use Lightna\Engine\App\Build;
use Lightna\Engine\App\Exception\LightnaException;

class Url extends DataA
{
    protected Build $build;
    protected Request $request;
    protected array $assetHashes;
    /** @AppConfig(asset_base) */
    protected string $assetBase;

    /** @noinspection PhpUnused */
    protected function defineAssetHashes(): void
    {
        $this->assetHashes = $this->build->load('asset/hashes');
    }

    public function asset(string $url, bool $escape = true): string
    {
        $url = $this->parseAssetUrl($url);
        if (!isset($this->assetHashes[$url->path])) {
            throw new LightnaException('Unknown asset "' . $url->orig . '", make sure it contains path to the module and is relative"');
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

    public function current(array $update = []): string
    {
        $params = $_GET;
        foreach ($update as $name => $value) {
            if (is_null($value) || $value === '') {
                unset($params[$name]);
            } else {
                $params[$name] = $value;
            }
        }

        $query = http_build_query($params);

        return '/' . $this->request->uriPath . ($query !== '' ? '?' . $query : '');
    }
}
