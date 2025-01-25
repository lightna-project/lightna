<?php

declare(strict_types=1);

namespace Lightna\Magento\Data\Product;

use Lightna\Engine\Data\DataA;
use Lightna\Magento\Data\Session;

/**
 * @method string regular(string $escapeMethod = null)
 * @method string final(string $escapeMethod = null)
 * @method string discount(string $escapeMethod = null)
 * @method string discountPercent(string $escapeMethod = null)
 */
class Price extends DataA
{
    public float $regular;
    public float $final;
    public float $discount;
    public float $discountPercent;

    protected Session $session;

    protected function init(array $data = []): void
    {
        $group = $this->session->customer->groupId;
        $priceGroup = isset($data['finalPrices'][$group]) ? $group : 0;

        $data = array_merge($data, [
            'final' => $data['finalPrices'][$priceGroup],
            'discount' => $data['discounts'][$priceGroup],
            'discountPercent' => $data['discountPercents'][$priceGroup],
        ]);

        parent::init($data);
    }
}
