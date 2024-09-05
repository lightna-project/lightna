<p align="center">
<a href="https://lightna.com">
<img src="https://lightna.com/asset/Lightna-Front/image/lightna-logo.svg" height="33 alt="Lightna Logo"/>
</a>
</p>

<h1 align="center">Welcome to the Lightna project!</h1>

<p align="center">
We develop Lightna Engine and MVP for Magento 2 Storefront.<br>
Readiness - 75%.
</p>

<p align="center">
<a href="https://lightna.com">About Lightna</a>
 | <a href="https://lightna.com/magento.html">Magento 2 MVP</a>
 | <a href="https://lightna.com/benchmark.html">Benchmark</a>
 | <a href="https://lightna.com/contact.html">Contact</a>
</p>

> [!IMPORTANT]
> * The project is still in development. You are welcome to contribute and experiment with it, but it is not yet ready for practical use
> * The documentation, including the contribution onboarding, will be supplemented soon
> * The number of functional and architectural points hasn't been implemented yet
> * The architecture, structure, and code are not final and subject to change
> * Please avoid using large catalogs, this feature is currently in development

# Contributor Setup

### Setup Magento OS
* Setup Magento OS into `project/magento-os` folder following standard instruction


### Setup Lightna
* Meet requirements
  * Ubuntu or macOS 
  * PHP 8.1
  * yaml extension
  * redis extension


* Initialize packages:
```
cd entry/magento-os && \
composer install && \
npm install && \
cd ../..
```


### Integrate Lightna and Magento
* Symlink Lightna module and theme:
```
mkdir -p project/magento-os/app/code/Lightna && \
mkdir -p project/magento-os/app/design/frontend/Lightna && \
ln -s ../../../../../code/magento-os/lightna-frontend project/magento-os/app/code/Lightna/Frontend && \
ln -s ../../../../../../code/magento-os/lightna-theme project/magento-os/app/design/frontend/Lightna/Lightna && \
```

* Redirect Magento index.php to Lightna:
```
mv project/magento-os/pub/index.php project/magento-os/pub/magento_index.php && \
ln -s ../../../entry/magento-os/index.php project/magento-os/pub/index.php
```

* Change current Magento theme to Lightna


* Enable Lightna theme and module:
```
project/magento-os/bin/magento setup:upgrade
```

### Configure Lightna
* `cp code/lightna/magento-os-backend/env.php.sample entry/magento-os/env.php`
* Edit `entry/magento-os/env.php` and define `*****` with your values
* Ensure that the Elasticsearch and Session settings correspond to the Magento setup, and note that currently, only file storage for sessions is supported
* Ensure that `session.serialize_handler` value is set to `php_serialize`


### Build
```
cd entry/magento-os && \
composer lightna:build
```


### Setup Lightna indexer schema
```
./cli indexer:schema:update
```


### Schedule Lightna indexer
```
* * * * * { cd [abs_path_to_repo]/entry/magento-os && ./cli indexer:process; } 2>&1 >> [abs_path_to_logs]/lightna-indexer.log
```
