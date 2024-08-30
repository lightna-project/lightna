<?php

declare(strict_types=1);

namespace Lightna\Magento\Data\Product;

use Lightna\Engine\Data\DataA;
use Lightna\Magento\Data\Session;

/**
 * @method float regular(string $escapeMethod = null)
 * @method float final(string $escapeMethod = null)
 * @method float discount(string $escapeMethod = null)
 * @method float discountPercent(string $escapeMethod = null)
 */
class Price extends DataA
{
    public float $regular;
    public float $final;
    public float $discount;
    public float $discountPercent;

    protected Session $session;

    protected function init($data = []): void
    {
        $group = $this->session->user->groupId;

        $data = array_merge($data, [
            'final' => $data['finalPrices'][$group],
            'discount' => $data['discounts'][$group],
            'discountPercent' => $data['discountPercents'][$group],
        ]);

        parent::init($data);
    }
}
