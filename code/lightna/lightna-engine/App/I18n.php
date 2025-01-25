<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

use MessageFormatter;

class I18n extends ObjectA
{
    protected Build $build;
    protected array $translates;

    public function phrase(string $phrase, array $args = []): string
    {
        $translates = $this->getTranslates();

        if (isset($translates[$phrase])) {
            if (!count($args)) {
                return $translates[$phrase][1];
            } else {
                return $this->translate($translates[$phrase], $args);
            }
        } else {
            return $phrase;
        }
    }

    protected function translate(array $translate, array $args): string
    {
        return match ($translate[0]) {
            'm' => $this->translateMessageFormatter($translate[1], $args),
            'k' => $this->translateKeys($translate[1], $args),
        };
    }

    protected function translateMessageFormatter(string $pattern, array $args): string
    {
        $result = MessageFormatter::formatMessage($this->getScopeLocale(), $pattern, $args);

        return $result !== false ? $result : $pattern;
    }

    protected function translateKeys(string $pattern, array $args): string
    {
        $keys = array_map(
            fn($key) => '%' . (is_int($key) ? $key + 1 : $key),
            array_keys($args),
        );

        return strtr($pattern, array_combine($keys, $args));
    }

    protected function getTranslates(): array
    {
        $locale = $this->getScopeLocale();
        $this->defineLocaleTranslates($locale);

        return $this->translates[$locale];
    }

    protected function getScopeLocale(): string
    {
        // Extension point

        return 'en_US';
    }

    protected function defineLocaleTranslates(string $locale): void
    {
        if (!isset($this->translates[$locale])) {
            $this->translates[$locale] = $this->loadLocaleTranslates($locale);
        }
    }

    protected function loadLocaleTranslates(string $locale): array
    {
        return $this->build->load('translate/' . $locale);
    }
}
