<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Index\Provider;

use Lightna\Engine\App\ObjectA;
use Lightna\Magento\Backend\App\Query\Cms\Page as MagentoCmsPage;
use Lightna\Magento\Backend\App\Query\Config as MagentoConfig;

class Config extends ObjectA
{
    protected MagentoConfig $magentoConfig;
    protected MagentoCmsPage $magentoCmsPage;
    /** @AppConfig(backend:magento/configuration/list) */
    protected array $list;
    /** @AppConfig(backend:logo/default) */
    protected array $defaultLogo;
    /** @AppConfig(backend:favicon/default) */
    protected array $defaultFavicon;

    public function getData(): array
    {
        $config = $this->magentoConfig->get();
        $config = $this->filterConfig($config);
        $this->complete($config);

        return $config;
    }

    protected function filterConfig(array $config): array
    {
        $filtered = [];
        foreach ($this->list as $dest => $path) {
            array_path_set(
                $filtered,
                $dest,
                array_path_get($config, $path)
            );
        }

        return $filtered;
    }

    protected function complete(array &$config): void
    {
        $this->updateLocale($config);
        $this->updateLogo($config);
        $this->updateFavicon($config);
        $this->updateCopyright($config);
        $this->updateNoRoutePageId($config);
    }

    protected function updateLocale(array &$config): void
    {
        $config['locale']['lang'] = strtok(array_path_get($config, 'locale/code'), '_');
    }

    protected function updateLogo(array &$config): void
    {
        if (!empty($config['logo']['src'])) {
            $config['logo']['src'] = '/media/logo/' . $config['logo']['src'];
        }

        $config['logo'] = merge(
            $this->defaultLogo,
            array_filter($config['logo']),
        );
    }

    protected function updateFavicon(array &$config): void
    {
        if (!empty($config['favicon']['href'])) {
            $config['favicon']['href'] = '/media/favicon/' . $config['favicon']['href'];
        } else {
            $config['favicon'] = $this->defaultFavicon;
        }
    }

    protected function updateCopyright(array &$config): void
    {
        if (!empty($config['copyright'])) {
            $config['copyright'] = str_replace('YYYY', date('Y'), $config['copyright']);
        }
    }

    protected function updateNoRoutePageId(array &$config): void
    {
        $identifier = $config['noRoutePageIdentifier'] ?? 'no-route';

        // Parse route in case the value is "identifier|page_id"
        $identifier = explode('|', $identifier)[0];

        unset($config['noRoutePageIdentifier']);
        $config['noRoutePageId'] = $this->magentoCmsPage->getByIdentifier($identifier)['page_id'] ?? null;
    }
}
