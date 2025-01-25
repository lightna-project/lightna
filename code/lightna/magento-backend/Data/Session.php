<?php

declare(strict_types=1);

namespace Lightna\Magento\Data;

use Lightna\Magento\Data\Session\Cart;
use Lightna\Magento\Data\Session\Customer;

class Session extends \Lightna\Session\Data\Session
{
    public Customer $customer;
    public Cart $cart;
}
