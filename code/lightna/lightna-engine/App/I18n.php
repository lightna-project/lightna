<?php

declare(strict_types=1);

namespace Lightna\Engine\App;

class I18n extends ObjectA
{
    public function phrase(string $phrase): string
    {
        return $phrase;
    }
}
