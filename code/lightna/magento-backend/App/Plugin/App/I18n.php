<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Plugin\App;

use Lightna\Engine\App\Context;
use Lightna\Engine\App\ObjectA;
use Lightna\Magento\Backend\Data\Config;

class I18n extends ObjectA
{
    protected Context $context;
    protected Config $config;
    protected array $scopeConfig;

    /** @noinspection PhpUnused */
    public function getScopeLocaleExtended(): string
    {
        if (LIGHTNA_AREA === 'frontend') {
            return $this->getFrontendLocale();
        } else {
            return $this->getBackendLocale();
        }
    }

    protected function getFrontendLocale(): string
    {
        return $this->config->locale->code;
    }

    protected function getBackendLocale(): string
    {
        return $this->getScopeConfig()->locale->code;
    }

    protected function getScopeConfig(): Config
    {
        $scope = $this->context->scope;

        if (!isset($this->scopeConfig[$scope])) {
            $this->scopeConfig[$scope] = newobj(Config::class);
        }

        return $this->scopeConfig[$scope];
    }
}
