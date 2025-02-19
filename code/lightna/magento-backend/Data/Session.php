<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data;

use Lightna\Magento\Backend\Data\Session\Cart;
use Lightna\Magento\Backend\Data\Session\Customer;

class Session extends \Lightna\Session\Data\Session
{
    public Customer $customer;
    public Cart $cart;
}
