<?php
return array (
  '.' => 
  array (
    'head' => 
    array (
      'container' => 'head',
      '.' => 
      array (
        'meta' => 
        array (
          '.' => 
          array (
            'encoding' => 
            array (
              'template' => 'page/head/meta/encoding.phtml',
              '.' => 
              array (
              ),
            ),
            'viewport' => 
            array (
              'template' => 'page/head/meta/viewport.phtml',
              '.' => 
              array (
              ),
            ),
          ),
        ),
        'title' => 
        array (
          'template' => 'page/head/title.phtml',
          '.' => 
          array (
          ),
        ),
        'link' => 
        array (
          '.' => 
          array (
            'favicon' => 
            array (
              'template' => 'page/head/link/favicon.phtml',
              '.' => 
              array (
              ),
            ),
            'font' => 
            array (
              'template' => 'page/head/link/font.phtml',
              '.' => 
              array (
              ),
            ),
            'stylesheet' => 
            array (
              'template' => 'page/head/link/style-common.phtml',
              '.' => 
              array (
              ),
            ),
          ),
        ),
        'script' => 
        array (
          'template' => 'page/head/js/common.phtml',
          '.' => 
          array (
          ),
        ),
        'ga' => 
        array (
          'template' => 'ga.phtml',
          '.' => 
          array (
          ),
        ),
      ),
    ),
    'body' => 
    array (
      'container' => 'body',
      '.' => 
      array (
        'server-time-container' => 
        array (
          'template' => 'server-time-container.phtml',
          '.' => 
          array (
          ),
        ),
        'usp' => 
        array (
          'template' => 'page/usp.phtml',
          '.' => 
          array (
          ),
        ),
        'header' => 
        array (
          'container' => 'header',
          '.' => 
          array (
            'inner' => 
            array (
              'container' => 'div',
              'attributes' => 
              array (
                'class' => 
                array (
                  0 => 'header__container',
                ),
              ),
              '.' => 
              array (
                'mobile-menu-toggle' => 
                array (
                  'template' => 'page/header/mobile-menu-toggle.phtml',
                  '.' => 
                  array (
                  ),
                ),
                'logo' => 
                array (
                  'template' => 'page/header/logo.phtml',
                  '.' => 
                  array (
                  ),
                ),
                'menu' => 
                array (
                  'template' => 'page/menu.phtml',
                  '.' => 
                  array (
                    'item' => 
                    array (
                      'template' => 'page/menu/item.phtml',
                      '.' => 
                      array (
                      ),
                    ),
                  ),
                ),
                'header-right' => 
                array (
                  'container' => 'div',
                  'attributes' => 
                  array (
                    'class' => 
                    array (
                      0 => 'header__right',
                    ),
                  ),
                  '.' => 
                  array (
                    'account' => 
                    array (
                      'template' => 'page/header/account.phtml',
                      '.' => 
                      array (
                        'guest' => 
                        array (
                          'template' => 'page/header/account/dropdown-guest.phtml',
                          '.' => 
                          array (
                          ),
                        ),
                        'customer' => 
                        array (
                          'template' => 'page/header/account/dropdown-customer.phtml',
                          'links' => 
                          array (
                            'overview' => 
                            array (
                              'url' => '/customer/account',
                              'label' => 'My Account',
                            ),
                            'orders' => 
                            array (
                              'url' => '/sales/order/history',
                              'label' => 'My Orders',
                            ),
                            'wishlist' => 
                            array (
                              'url' => '/wishlist',
                              'label' => 'My Wishlist',
                            ),
                            'logout' => 
                            array (
                              'url' => '/customer/account/logout',
                              'label' => 'Logout',
                            ),
                          ),
                          '.' => 
                          array (
                          ),
                        ),
                      ),
                    ),
                    'minicart' => 
                    array (
                      'id' => 'minicart',
                      'template' => 'page/header/minicart.phtml',
                      '.' => 
                      array (
                        'content' => 
                        array (
                          'template' => 'page/header/minicart/content.phtml',
                          '.' => 
                          array (
                            'empty' => 
                            array (
                              'template' => 'page/header/minicart/empty.phtml',
                              '.' => 
                              array (
                              ),
                            ),
                            'products' => 
                            array (
                              'template' => 'page/header/minicart/products-list.phtml',
                              '.' => 
                              array (
                                'item' => 
                                array (
                                  'container' => 'li',
                                  'attributes' => 
                                  array (
                                    'class' => 
                                    array (
                                      0 => 'products-list__item',
                                    ),
                                  ),
                                  '.' => 
                                  array (
                                    'image' => 
                                    array (
                                      'template' => 'page/header/minicart/product-item/image.phtml',
                                      '.' => 
                                      array (
                                      ),
                                    ),
                                    'info' => 
                                    array (
                                      'container' => 'div',
                                      'attributes' => 
                                      array (
                                        'class' => 
                                        array (
                                          0 => 'products-list__item__info',
                                        ),
                                      ),
                                      '.' => 
                                      array (
                                        'name' => 
                                        array (
                                          'template' => 'page/header/minicart/product-item/name.phtml',
                                          '.' => 
                                          array (
                                          ),
                                        ),
                                        'sku' => 
                                        array (
                                          'template' => 'page/header/minicart/product-item/sku.phtml',
                                          '.' => 
                                          array (
                                          ),
                                        ),
                                        'quantity' => 
                                        array (
                                          'template' => 'page/header/minicart/product-item/quantity.phtml',
                                          '.' => 
                                          array (
                                          ),
                                        ),
                                        'price' => 
                                        array (
                                          'template' => 'page/header/minicart/product-item/price.phtml',
                                          '.' => 
                                          array (
                                          ),
                                        ),
                                      ),
                                    ),
                                    'remove' => 
                                    array (
                                      'template' => 'page/header/minicart/product-item/remove.phtml',
                                      '.' => 
                                      array (
                                      ),
                                    ),
                                  ),
                                ),
                              ),
                            ),
                            'footer' => 
                            array (
                              'template' => 'page/header/minicart/footer.phtml',
                              '.' => 
                              array (
                              ),
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ),
        ),
        'main' => 
        array (
          'container' => 'div',
          'attributes' => 
          array (
            'class' => 
            array (
              0 => 'page-container',
            ),
          ),
          '.' => 
          array (
            'content' => 
            array (
              'container' => 'div',
              '.' => 
              array (
                'details' => 
                array (
                  'template' => 'category/details.phtml',
                  '.' => 
                  array (
                  ),
                ),
                'columns' => 
                array (
                  'container' => 'div',
                  'attributes' => 
                  array (
                    'class' => 
                    array (
                      0 => 'grid',
                      1 => 'gap-6',
                      2 => 'md:gap-12',
                      3 => 'md:grid-cols-[280px_1fr]',
                    ),
                  ),
                  '.' => 
                  array (
                    'facets' => 
                    array (
                      'template' => 'category/facets.phtml',
                      '.' => 
                      array (
                        'toggle-facets' => 
                        array (
                          'template' => 'component/toggle-link.phtml',
                          '.' => 
                          array (
                          ),
                        ),
                      ),
                    ),
                    'result' => 
                    array (
                      'template' => 'category/result.phtml',
                      '.' => 
                      array (
                        'item' => 
                        array (
                          'container' => 'li',
                          'attributes' => 
                          array (
                            'class' => 
                            array (
                              0 => 'product-item',
                            ),
                          ),
                          '.' => 
                          array (
                            'top' => 
                            array (
                              'container' => 'div',
                              'attributes' => 
                              array (
                                'class' => 
                                array (
                                  0 => 'product-item__top',
                                ),
                              ),
                              '.' => 
                              array (
                                'image' => 
                                array (
                                  'template' => 'category/product-item/image.phtml',
                                  '.' => 
                                  array (
                                  ),
                                ),
                              ),
                            ),
                            'bottom' => 
                            array (
                              'container' => 'div',
                              'attributes' => 
                              array (
                                'class' => 
                                array (
                                  0 => 'product-item__bottom',
                                ),
                              ),
                              '.' => 
                              array (
                                'sku' => 
                                array (
                                  'template' => 'product/attributes/sku.phtml',
                                  '.' => 
                                  array (
                                  ),
                                ),
                                'name' => 
                                array (
                                  'template' => 'category/product-item/name.phtml',
                                  '.' => 
                                  array (
                                  ),
                                ),
                                'price-container' => 
                                array (
                                  'container' => 'div',
                                  'attributes' => 
                                  array (
                                    'class' => 
                                    array (
                                      0 => 'product-item__price',
                                    ),
                                  ),
                                  '.' => 
                                  array (
                                    'price' => 
                                    array (
                                      'template' => 'product/price.phtml',
                                      '.' => 
                                      array (
                                      ),
                                    ),
                                  ),
                                ),
                              ),
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ),
        ),
        'footer' => 
        array (
          'container' => 'footer',
          '.' => 
          array (
            'menu' => 
            array (
              'template' => 'page/footer/menu.phtml',
              '.' => 
              array (
              ),
            ),
            'copyright' => 
            array (
              'template' => 'page/footer/copyright.phtml',
              '.' => 
              array (
              ),
            ),
          ),
        ),
        'messages-container' => 
        array (
          'container' => 'div',
          'attributes' => 
          array (
            'class' => 
            array (
              0 => 'page-messages',
            ),
          ),
          '.' => 
          array (
            'messages' => 
            array (
              'id' => 'messages',
              'template' => 'page/messages.phtml',
              '.' => 
              array (
              ),
            ),
          ),
        ),
        'server-time' => 
        array (
          'template' => 'server-time.phtml',
          '.' => 
          array (
          ),
        ),
      ),
      'attributes' => 
      array (
        'class' => 
        array (
          0 => 'category',
        ),
      ),
    ),
  ),
  'template' => 'page.phtml',
  'directives' => 
  array (
    0 => 'position .body.server-time-container first',
    1 => 'position .body.server-time last',
  ),
  'extends' => 
  array (
    0 => 'page',
  ),
  'blockById' => 
  array (
    'minicart' => '/body/header/inner/header-right/minicart',
    'messages' => '/body/messages-container/messages',
  ),
);