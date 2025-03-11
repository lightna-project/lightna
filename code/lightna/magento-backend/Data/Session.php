<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data;

use Lightna\Magento\Backend\Data\Session\Cart;
use Lightna\Magento\Backend\Data\Session\Customer;
use Lightna\Session\Data\Session as SessionData;

class Session extends SessionData
{
    public Customer $customer;
    public Cart $cart;
}
