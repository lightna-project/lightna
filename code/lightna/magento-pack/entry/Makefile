MAKEFLAGS += --no-print-directory

install:
	composer install
	npm install

build:
	LIGHTNA_EDITION=main make _build
	make update

_build:
	./cli build.compile
	make _build.js
	make _build.css
	./cli build.asset.sign
	./cli build.apply
	./cli config.apply

_build.js:
	export LIGHTNA_EDITION_DIR=$$(./cli build.config edition_dir) && \
	npm run webpack

_build.css:
	`./cli build.config edition_dir`/building/tailwind/build.sh

build.update:
	LIGHTNA_EDITION=main make _build.update

_build.update:
	export LIGHTNA_EDITION_DIR=$$(./cli config edition_dir) && \
	export LIGHTNA_EDITION_ASSET_DIR=$$(./cli config edition_asset_dir) && \
	make __build.update

__build.update:
	make _build.compile.direct
	./cli config.apply
	make _build.update.js
	make _build.update.css
	./cli build.asset.sign --direct

_build.update.js:
	npm run webpack -- --config webpack.config.direct.js

_build.update.css:
	`./cli config edition_dir`/build/tailwind/update.sh

compile:
	LIGHTNA_EDITION=main make _build.compile.direct

_build.compile.direct:
	./cli build.compile --direct
	./cli config.apply

update:
	./cli update

config.apply:
	LIGHTNA_EDITION=main ./cli config.apply

test.unit:
	LIGHTNA_EDITION=main make _test.unit

_test.unit:
	vendor/bin/phpunit -c `./cli config edition_dir`/build/phpunit/config.unit.xml

test.integration:
	LIGHTNA_EDITION=main make _test.integration

_test.integration:
	vendor/bin/phpunit -c `./cli config edition_dir`/build/phpunit/config.integration.xml

watch.main.js:
	export LIGHTNA_EDITION=main; \
	export LIGHTNA_EDITION_DIR=$$(./cli config edition_dir); \
	export LIGHTNA_EDITION_ASSET_DIR=$$(./cli config edition_asset_dir); \
	npm run webpack -- --mode=development --config webpack.config.direct.js

watch.main.common.css:
	LIGHTNA_EDITION=main; \
	npx tailwindcss \
		-i `./cli config edition_dir`/build/tailwind/common/entry.css \
		-o `./cli config edition_asset_dir`/build/style/common.css -- --watch --verbose
