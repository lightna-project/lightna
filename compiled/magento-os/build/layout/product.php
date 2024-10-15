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
      'attributes' => 
      array (
        'prefix' => 'og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# product: http://ogp.me/ns/product#',
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
              'attributes' => 
              array (
                'class' => 
                array (
                  0 => 'grid',
                  1 => 'gap-6',
                  2 => 'lg:gap-x-20',
                  3 => 'md:gap-8',
                  4 => 'md:grid-cols-[auto_40%]',
                  5 => 'items-start',
                ),
              ),
              '.' => 
              array (
                'gallery' => 
                array (
                  'template' => 'product/gallery.phtml',
                  '.' => 
                  array (
                  ),
                ),
                'main' => 
                array (
                  'container' => 'div',
                  'attributes' => 
                  array (
                    'class' => 
                    array (
                      0 => 'md:sticky',
                      1 => 'top-[88px]',
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
                      'template' => 'product/attributes/name.phtml',
                      '.' => 
                      array (
                      ),
                    ),
                    'price-and-discount' => 
                    array (
                      'container' => 'div',
                      'attributes' => 
                      array (
                        'class' => 
                        array (
                          0 => 'flex',
                          1 => 'items-center',
                          2 => 'gap-x-3',
                          3 => 'gap-y-1',
                          4 => 'flex-wrap-reverse',
                          5 => 'pb-2',
                          6 => 'mb-4',
                          7 => 'price-lg',
                        ),
                      ),
                      '.' => 
                      array (
                        'price' => 
                        array (
                          'template' => 'product/price.phtml',
                          'attributes' => 
                          array (
                            'class' => 
                            array (
                              0 => 'flex',
                              1 => 'items-center',
                              2 => 'flex-wrap',
                              3 => 'gap-x-3',
                              4 => 'gap-y-1',
                            ),
                          ),
                          '.' => 
                          array (
                          ),
                        ),
                        'discount' => 
                        array (
                          'template' => 'product/discount.phtml',
                          '.' => 
                          array (
                          ),
                        ),
                      ),
                    ),
                    'cta' => 
                    array (
                      '.' => 
                      array (
                        'add-to-cart' => 
                        array (
                          'template' => 'product/cta/add-to-cart.phtml',
                          'attributes' => 
                          array (
                            'class' => 
                            array (
                              0 => 'cjs-add-to-cart',
                              1 => 'flex',
                              2 => 'flex-wrap',
                              3 => 'gap-4',
                            ),
                          ),
                          '.' => 
                          array (
                            'options' => 
                            array (
                              'id' => 'product-options',
                              'template' => 'product/options.phtml',
                              'attributes' => 
                              array (
                                'class' => 
                                array (
                                  0 => 'cjs-product-options',
                                  1 => 'w-full',
                                  2 => 'flex',
                                  3 => 'flex-col',
                                  4 => 'gap-2',
                                ),
                              ),
                              '.' => 
                              array (
                              ),
                            ),
                          ),
                        ),
                      ),
                    ),
                    'stock' => 
                    array (
                      'template' => 'product/stock.phtml',
                      '.' => 
                      array (
                      ),
                    ),
                    'usp' => 
                    array (
                      'template' => 'product/usp.phtml',
                      '.' => 
                      array (
                      ),
                    ),
                  ),
                ),
                'details' => 
                array (
                  'template' => 'product/tabs.phtml',
                  '.' => 
                  array (
                    'description' => 
                    array (
                      'title' => 'Description',
                      'active' => true,
                      'template' => 'product/description.phtml',
                      '.' => 
                      array (
                      ),
                    ),
                    'specifications' => 
                    array (
                      'title' => 'Specifications',
                      'template' => 'product/attributes/all-visible.phtml',
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
          0 => 'product',
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
    'product-options' => '/body/main/content/main/cta/add-to-cart/options',
    'messages' => '/body/messages-container/messages',
  ),
);