<?php

declare(strict_types=1);

namespace Lightna\Session\App\Session;

use Lightna\Engine\App\Build;
use Lightna\Engine\App\Context;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\State\Common as AppState;

class DataBuilder extends ObjectA
{
    /** @AppConfig(session) */
    protected array $config;
    protected Context $context;
    protected Build $build;
    protected AppState $appState;
    protected string $scopeKey;
    protected array $sessionData;
    protected array $scopeData;
    protected array $scopeDataOrig;
    protected bool $forceReindex;
    protected bool $isReindexRequired;

    public function setSessionData(array $sessionData): static
    {
        $this->forceReindex = $this->isReindexRequired = false;
        $this->sessionData = $sessionData;
        $this->scopeData = $sessionData[$this->scopeKey] ?? [];
        $this->scopeData += $this->getDefaultValues();
        $this->scopeDataOrig = $this->scopeData;

        return $this;
    }

    public function setField(string $field, mixed $value): static
    {
        $this->scopeData['data'][$field] = $value;

        return $this;
    }

    public function forceReindex(bool $forceReindex): static
    {
        $this->forceReindex = $forceReindex;

        return $this;
    }

    public function getIsReindexRequired(): bool
    {
        return $this->isReindexRequired;
    }

    public function unsetField(string $field): static
    {
        unset($this->scopeData['data'][$field]);

        return $this;
    }

    public function getSessionData(): array
    {
        $this->updateDataIndex();
        $this->sessionData[$this->scopeKey] = $this->scopeData;

        return $this->sessionData;
    }

    public function getScopeData(): array
    {
        $this->updateDataIndex();

        return $this->scopeData;
    }

    /** @noinspection PhpUnused */
    protected function defineScopeKey(): void
    {
        $scope = $this->config['scoped'] ? $this->context->scope : '*';
        $this->scopeKey = 'scope_' . $scope;
    }

    protected function getDefaultValues(): array
    {
        return [
            'data' => [],
            'index' => [],
            'meta' => ['version' => 0, 'build' => 0],
        ];
    }

    protected function updateDataIndex(): void
    {
        $origData = $this->scopeDataOrig['data'];
        $newData = $this->scopeData['data'];

        if (!$this->isReindexRequired($origData, $newData)) {
            return;
        }

        $this->scopeData['index'] = $this->reindex($newData);
        $this->scopeData['meta']['version'] = $this->appState->session->version;
        $this->scopeData['meta']['build'] = $this->build->getVersion();
    }

    protected function isReindexRequired(array $origData, array $newData): bool
    {
        // Extension point

        $this->isReindexRequired = $this->forceReindex || $this->isIndexOutdated();

        return $this->isReindexRequired;
    }

    protected function isIndexOutdated(): bool
    {
        if ($this->scopeData['data'] === []) {
            // Ignore empty guest session!
            return false;
        }

        $meta = $this->scopeData['meta'];
        $isVersionOutdated = $meta['version'] !== $this->appState->session->version;
        $isBuildOutdated = $meta['build'] !== $this->build->getVersion();

        return $isVersionOutdated || $isBuildOutdated;
    }

    protected function reindex(array $data): array
    {
        // Extension point

        return [];
    }
}
