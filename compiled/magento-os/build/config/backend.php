<?php
return array (
  'indexer' => 
  array (
    'changelog' => 
    array (
      'tables' => 
      array (
        'include' => 
        array (
          '^catalog_product_entity' => 
          array (
            0 => 'entity_id',
          ),
          '^catalog_product_index_price$' => 
          array (
          ),
          '^catalog_product_relation$' => 
          array (
          ),
          '^catalog_product_super_attribute$' => 
          array (
            0 => 'product_id',
          ),
          '^cms_page' => 
          array (
          ),
          '^cataloginventory_stock_item$' => 
          array (
            0 => 'product_id',
          ),
          '^catalog_product_website$' => 
          array (
            0 => 'product_id',
          ),
          '^url_rewrite$' => 
          array (
            0 => 'entity_type',
            1 => 'entity_id',
          ),
          '^core_config_data$' => 
          array (
          ),
          '^catalog_category_entity' => 
          array (
            0 => 'entity_id',
          ),
          '^eav_attribute$' => 
          array (
          ),
          '^eav_attribute_label$' => 
          array (
          ),
          '^eav_attribute_option$' => 
          array (
          ),
          '^eav_attribute_option_value$' => 
          array (
          ),
          '^catalog_eav_attribute$' => 
          array (
          ),
          '^cms_block' => 
          array (
          ),
          '^inventory_source_item$' => 
          array (
            0 => 'sku',
          ),
          '^inventory_reservation$' => 
          array (
            0 => 'sku',
          ),
        ),
        'exclude' => 
        array (
          0 => '_(cl|tmp|idx|replica)$',
          1 => '^sequence_',
          2 => '^catalog_product_entity_media_gallery',
        ),
      ),
      'batch' => 
      array (
        'handlers' => 
        array (
          'lightna_product_handler' => 'Lightna\\Magento\\App\\Index\\Changelog\\ProductBatchHandler',
          'lightna_cms_page_handler' => 'Lightna\\Magento\\App\\Index\\Changelog\\CmsPageBatchHandler',
          'lightna_stock_handler' => 'Lightna\\Magento\\App\\Index\\Changelog\\StockBatchHandler',
          'lightna_inventory_handler' => 'Lightna\\Magento\\App\\Index\\Changelog\\InventoryBatchHandler',
          'lightna_product_website_handler' => 'Lightna\\Magento\\App\\Index\\Changelog\\ProductWebsiteBatchHandler',
          'lightna_url_rewrite_handler' => 'Lightna\\Magento\\App\\Index\\Changelog\\UrlRewriteBatchHandler',
          'lightna_config_handler' => 'Lightna\\Magento\\App\\Index\\Changelog\\ConfigBatchHandler',
          'lightna_category_handler' => 'Lightna\\Magento\\App\\Index\\Changelog\\CategoryBatchHandler',
          'lightna_attributes_handler' => 'Lightna\\Magento\\App\\Index\\Changelog\\AttributesBatchHandler',
          'lightna_cms_block_handler' => 'Lightna\\Magento\\App\\Index\\Changelog\\CmsBlockBatchHandler',
        ),
      ),
    ),
  ),
  'asset_dir' => '../../project/magento-os/pub/static/lightna',
  'storage' => 
  array (
    'opcache' => 
    array (
      'adapter' => 'Lightna\\Engine\\App\\Storage\\Opcache',
      'options' => 
      array (
        'dir' => 'opcache',
      ),
    ),
    'redis' => 
    array (
      'adapter' => 'Lightna\\Redis\\App\\Storage\\Redis',
      'options' => 
      array (
        'host' => 'localhost',
        'port' => '6379',
        'db' => 0,
        'persistent' => true,
      ),
    ),
  ),
  'router' => 
  array (
    'action' => 
    array (
      'page' => 'Lightna\\Engine\\App\\Router\\Action\\Page',
    ),
    'bypass' => 
    array (
      'process_after_routing' => false,
      'rules' => 
      array (
        'url_starts_with' => 
        array (
          0 => 'checkout/',
          1 => 'customer/',
          2 => 'sales/',
          3 => 'wishlist/',
          4 => 'downloadable/customer/products/',
          5 => 'newsletter/manage/',
          6 => 'vault/cards/listaction/',
          7 => 'review/customer/',
          8 => 'soap/',
          9 => 'rest/',
          10 => 'admin_0b1p2FAZ(/|$)',
        ),
      ),
      'file' => '../../project/magento-os/pub/magento_index.php',
      'cookie' => 
      array (
        'enabled' => true,
        'name' => '___BYPASS',
      ),
    ),
    404 => 
    array (
      'action' => '',
    ),
  ),
  'entity' => 
  array (
    'route' => 
    array (
      'storage' => 'redis',
    ),
    'config' => 
    array (
      'entity' => 'Lightna\\Magento\\App\\Entity\\Config',
      'index' => 'Lightna\\Magento\\Index\\Config',
      'data' => 'Lightna\\Magento\\Data\\Config',
      'storage' => 'opcache',
    ),
    'page' => 
    array (
      'layout' => 'page',
      'storage' => 'redis',
    ),
    'no-route' => 
    array (
      'layout' => 'no-route',
      'storage' => 'redis',
    ),
    'url_rewrite' => 
    array (
      'entity' => 'Lightna\\Magento\\App\\Entity\\Route',
      'index' => 'Lightna\\Magento\\Index\\Route\\UrlRewrite',
      'storage' => 'redis',
    ),
    'cms' => 
    array (
      'entity' => 'Lightna\\Magento\\App\\Entity\\Cms',
      'index' => 'Lightna\\Magento\\Index\\Cms',
      'data' => 'Lightna\\Magento\\Data\\Cms',
      'layout' => 'cms',
      'storage' => 'redis',
    ),
    'product' => 
    array (
      'entity' => 'Lightna\\Magento\\App\\Entity\\Product',
      'index' => 'Lightna\\Magento\\Index\\Product',
      'data' => 'Lightna\\Magento\\Data\\Product',
      'layout' => 'product',
      'storage' => 'redis',
    ),
    'content_page' => 
    array (
      'entity' => 'Lightna\\Magento\\App\\Entity\\Content\\Page',
      'index' => 'Lightna\\Magento\\Index\\Content\\Page',
      'data' => 'Lightna\\Magento\\Data\\Content\\Page',
      'storage' => 'redis',
    ),
    'content_product' => 
    array (
      'entity' => 'Lightna\\Magento\\App\\Entity\\Content\\Product',
      'index' => 'Lightna\\Magento\\Index\\Content\\Product',
      'data' => 'Lightna\\Magento\\Data\\Content\\Product',
      'storage' => 'redis',
    ),
    'category' => 
    array (
      'entity' => 'Lightna\\Magento\\App\\Entity\\Category',
      'index' => 'Lightna\\Magento\\Index\\Category',
      'data' => 'Lightna\\Magento\\Data\\Category',
      'layout' => 'category',
      'storage' => 'redis',
    ),
    'content_category' => 
    array (
      'entity' => 'Lightna\\Magento\\App\\Entity\\Content\\Category',
      'index' => 'Lightna\\Magento\\Index\\Content\\Category',
      'data' => 'Lightna\\Magento\\Data\\Content\\Category',
      'storage' => 'redis',
    ),
  ),
  'cli' => 
  array (
    'command' => 
    array (
      'deploy:asset:sign' => 'Lightna\\Engine\\App\\Console\\Deploy\\AssetSign',
      'deploy:opcache' => 'Lightna\\Engine\\App\\Console\\Deploy\\Opcache',
      'deploy:data' => 'Lightna\\Engine\\App\\Console\\Deploy\\Data',
      'indexer:schema:update' => 'Lightna\\Engine\\App\\Console\\Indexer\\UpdateSchema',
      'indexer:process' => 'Lightna\\Engine\\App\\Console\\Indexer\\Process',
    ),
  ),
  'default' => 
  array (
    'storage' => 'redis',
  ),
  'plugin' => 
  array (
    'Lightna\\Engine\\App' => 
    array (
      0 => 'Lightna\\Session\\App\\Plugin\\App',
      1 => 'Lightna\\Magento\\App\\Plugin\\App',
    ),
    'Lightna\\Engine\\App\\Scope' => 
    array (
      0 => 'Lightna\\Magento\\App\\Plugin\\App\\Scope',
    ),
    'Lightna\\Engine\\App\\Index\\Changelog\\Handler' => 
    array (
      0 => 'Lightna\\Magento\\App\\Plugin\\App\\Index\\Changelog\\Handler',
    ),
    'Lightna\\Session\\App\\Session\\Cookie' => 
    array (
      0 => 'Lightna\\Magento\\App\\Plugin\\App\\Session\\Cookie',
    ),
  ),
  'magento' => 
  array (
    'product' => 
    array (
      'blocks' => 
      array (
        'uspHtml' => 'product_usp',
      ),
    ),
    'configuration' => 
    array (
      'list' => 
      array (
        'country/code' => 'general/country/default',
        'locale/code' => 'general/locale/code',
        'store/name' => 'general/store_information/name',
        'logo/src' => 'design/header/logo_src',
        'logo/width' => 'design/header/logo_width',
        'logo/height' => 'design/header/logo_height',
        'logo/alt' => 'design/header/logo_alt',
        'favicon/href' => 'design/head/shortcut_icon',
        'copyright' => 'design/footer/copyright',
        'noRoutePageIdentifier' => 'web/default/cms_no_route',
        'currency/default' => 'currency/options/default',
        'ga/active' => 'google/analytics/active',
        'ga/account' => 'google/analytics/account',
        'session/cookie/lifetime' => 'web/cookie/cookie_lifetime',
        'product/listing/defaultPageSize' => 'catalog/frontend/grid_per_page',
      ),
    ),
    'page' => 
    array (
      'blocks' => 
      array (
        'footerLinksHtml' => 'footer_links',
        'uspHtml' => 'usp',
      ),
    ),
  ),
  'project' => 
  array (
    'connection' => 
    array (
      'host' => 'localhost',
      'port' => 3306,
      'username' => 'root',
      'password' => 'abcABC123',
      'dbname' => 'lightna_magento_os',
    ),
    'src_dir' => '../../project/magento-os',
  ),
  'logo' => 
  array (
    'default' => 
    array (
      'src' => 'magento-os-frontend/image/logo-default.svg',
      'width' => 105,
      'height' => 31,
      'alt' => 'Lightna',
    ),
  ),
  'favicon' => 
  array (
    'default' => 
    array (
      'href' => 'magento-os-frontend/image/favicon.ico',
    ),
  ),
  'src_dir' => '../../code/lightna/lightna-engine',
  'modules' => 
  array (
    'Lightna\\Redis' => '../../code/lightna/lightna-redis',
    'Lightna\\Session' => '../../code/lightna/lightna-session',
    'Lightna\\Elasticsearch' => '../../code/lightna/lightna-elasticsearch',
    'Lightna\\Magento' => '../../code/lightna/magento-os-backend',
    'Lightna\\Magento\\Frontend' => '../../code/lightna/magento-os-frontend',
    'Lightna\\Magento\\Demo' => '../../code/lightna/magento-os-demo',
  ),
  'libs' => 
  array (
    'Laminas\\Db' => 'vendor/laminas/laminas-db/src',
    'Laminas\\Stdlib' => 'vendor/laminas/laminas-stdlib/src',
  ),
  'compiler' => 
  array (
    'code_dir' => '../../compiled/magento-os',
  ),
  'mode' => 'dev',
  'doc_dir' => '../../project/magento-os/pub',
  'session' => 
  array (
    'handler' => 'Lightna\\Session\\App\\Handler\\File',
    'options' => 
    array (
      'cookie' => 
      array (
        'name' => 'PHPSESSID',
        'lifetime' => 3600,
        'secure' => false,
      ),
      'path' => '/var/lib/php/sessions',
      'prefix' => 'sess_',
    ),
  ),
  'elasticsearch' => 
  array (
    'connection' => 
    array (
      'host' => 'localhost',
      'port' => 9200,
    ),
    'prefix' => 'lightna_magento2',
  ),
  'asset_base' => '/static/lightna/',
);