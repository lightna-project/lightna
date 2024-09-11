<?php

declare(strict_types=1);

namespace Lightna\Magento\Data;

use Lightna\Engine\App\Context;
use Lightna\Engine\Data\DataA;
use Lightna\Magento\Data\Config\Currency;
use Lightna\Magento\Data\Config\Favicon;
use Lightna\Magento\Data\Config\GoogleAnalytics;
use Lightna\Magento\Data\Config\Logo;
use Lightna\Magento\Data\Config\Session;

/**
 * @method string copyright(string $escapeMethod = null)
 * @method string noRoutePageId(string $escapeMethod = null)
 */
class Config extends DataA
{
    public Currency $currency;
    public Favicon $favicon;
    public GoogleAnalytics $ga;
    public Locale $locale;
    public Logo $logo;
    public Session $session;
    public int $noRoutePageId;
    public string $copyright;

    /** @AppConfig(entity/config/entity) */
    protected string $configEntity;
    protected Context $context;

    protected function init($data = []): void
    {
        parent::init($this->getEntityData());
    }

    protected function getEntityData(): array
    {
        $configId = $this->context->scope;
        if (!$data = getobj($this->configEntity)->get($configId)) {
            throw new \Exception('Config data for "' . $configId . '" not found');
        }

        return $data;
    }
}
