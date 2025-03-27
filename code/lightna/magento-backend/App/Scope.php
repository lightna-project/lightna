<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App;

use Lightna\Engine\App\Exception\LightnaException;
use Lightna\Engine\App\ObjectA;
use Lightna\Magento\Backend\App\Entity\RunCode as RunCodeEntity;
use Lightna\Magento\Backend\App\Query\Store;

class Scope extends ObjectA
{
    protected RunCodeEntity $runCodeEntity;
    protected Store $store;

    protected string $runType;
    protected string $runCode;
    protected array $runCodes;

    public function resolve(): int
    {
        if (LIGHTNA_AREA !== 'frontend' || $this->runCode === '') {
            return 1;
        }

        if (!$scope = $this->runCodes[$this->runType][$this->runCode] ?? null) {
            throw new LightnaException('Undefined run code "' . $this->runCode . '"');
        }

        return $scope;
    }

    public function getList(): array
    {
        return array_keys($this->store->getList());
    }

    /** @noinspection PhpUnused */
    protected function defineRunType(): void
    {
        $this->runType = $_SERVER['MAGE_RUN_TYPE'] ?? '';
        if (!in_array($this->runType, ['website', 'store'])) {
            throw new LightnaException('Unsupported MAGE_RUN_TYPE value');
        }
    }

    /** @noinspection PhpUnused */
    protected function defineRunCode(): void
    {
        $this->runCode = camel($_SERVER['MAGE_RUN_CODE'] ?? '');
    }

    /** @noinspection PhpUnused */
    protected function defineRunCodes(): void
    {
        $this->runCodes = $this->runCodeEntity->get(1);
    }
}
