<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data;

use Lightna\Engine\Data\DataA;
use NumberFormatter;

class Currency extends DataA
{
    protected Config $config;
    protected NumberFormatter $numberFormatter;

    protected function init(array $data = []): void
    {
        parent::init($data);

        $this->numberFormatter = new NumberFormatter(
            $this->config->locale->code,
            NumberFormatter::CURRENCY,
        );
    }

    public function render(float|int|string $amount): string
    {
        return escape($this->numberFormatter->formatCurrency(
            (float)$amount,
            $this->config->currency->default,
        ));
    }

    public function renderSymbol(): string
    {
        $symbol = $this->numberFormatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);

        return escape($symbol);
    }
}
