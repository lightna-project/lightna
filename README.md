<p align="center">
<a href="https://lightna.com">
<img src="https://lightna.com/asset/Lightna-Front/image/lightna-logo.svg" height="33 alt="Lightna Logo"/>
</a>
</p>

<h1 align="center">Welcome to the Lightna project!</h1>

<p align="center">
We develop Lightna Engine and MVP for Magento 2 Storefront.<br>
Readiness of Lightna and Lightna Lane - 90%.<br>
Readiness of MVP for Magento 2 - 75%.
</p>

<p align="center">
<a href="https://lightna.com">Website</a>
 | <a href="https://lightna.com/lightna-engine.html">Lightna Engine</a>
 | <a href="https://lightna.com/lightna-lane.html">Lightna Lane</a>
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
* Setup Magento OS into `project/magento-os` folder following standard instruction and make sure Magento is configured and functioning correctly
* Set developer mode and allow symlinks:
```bash
php bin/magento deploy:mode:set developer
php bin/magento config:set dev/template/allow_symlink 1
```


### Setup Lightna
* Meet requirements
  * Ubuntu or macOS 
  * PHP 8.1, 8.2, 8.3
  * yaml extension
  * redis extension


* Initialize packages:
```
cd entry/magento-os && \
make install && \
cd ../..
```


### Integrate Lightna and Magento
* Symlink Lightna modules and themes:
```
export magentoAppDir=`realpath project/magento-os/app` && \
export lightnaModulesDir=`realpath code` && \
mkdir -p $magentoAppDir/code/Lightna && \
mkdir -p $magentoAppDir/design/frontend/Lightna && \
ln -sf $lightnaModulesDir/magento/lightna-frontend $magentoAppDir/code/Lightna/Frontend && \
ln -sf $lightnaModulesDir/magento/lightna-demo $magentoAppDir/code/Lightna/Demo && \
ln -sf $lightnaModulesDir/magento/lightna-theme $magentoAppDir/design/frontend/Lightna/Lightna && \
ln -sf $lightnaModulesDir/magento/lightna-lane-theme $magentoAppDir/design/frontend/Lightna/Lane
```

* Redirect Magento index.php to Lightna:
```
mv project/magento-os/pub/index.php project/magento-os/pub/magento_index.php && \
ln -sf ../../../entry/magento-os/index.php project/magento-os/pub/index.php
```

* Configure Lightna entry path in `app/etc/env.php`:
```
    'lightna_entry' => __DIR__ . '/../../../../entry/magento-os',
```

* Enable Lightna theme and module:
```
php project/magento-os/bin/magento setup:upgrade
```

* Change current Magento theme to `Lightna`


### Configure Lightna
* `cp code/lightna/magento-pack/entry/edition/main/env.php entry/magento-os/edition/main/env.php`
* Edit `entry/magento-os/env.php` and define `****` with your values
* Ensure that the Elasticsearch and Session settings correspond to the Magento setup, and note that currently, only file storage for sessions is supported
* Ensure that `session.serialize_handler` value is set to `php_serialize`


### Build & Reindex
```
cd entry/magento-os && \
make build && \
make schema.update && \
./cli index.update.all
```


### Schedule Lightna indexer
```
* * * * * { cd [abs_path_to_repo]/entry/magento-os && ./cli index.queue.watch; } 2>&1 >> [abs_path_to_logs]/lightna-index.log
```
