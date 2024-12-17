<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

class I18n extends ObjectA
{
    protected Build $build;

    protected string $locale;
    protected array $translates;

    protected function setLocale(string $locale): void
    {
        $this->locale = $locale;
        unset($this->translates);
    }

    /** @noinspection PhpUnused */
    protected function defineLocale(): void
    {
        // Extension point
        $this->locale = 'en_US';
    }

    /** @noinspection PhpUnused */
    protected function defineTranslates(): void
    {
        $this->translates = $this->build->load('translate/' . $this->locale);
    }

    public function phrase(string $phrase, array $args = []): string
    {
        $translate = $this->translates[$phrase] ?? $phrase;

        return sprintf($translate, ...$args);
    }
}
