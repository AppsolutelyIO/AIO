#!/usr/bin/env bash

cp -f ./tests/resources/stubs/artisan ./laravel-tests/
cp -f ./tests/resources/stubs/ComposerConfigCommand.php ./laravel-tests/app/
mkdir ./laravel-tests/aio
cp -rf ./config ./laravel-tests/aio
cp -rf ./database ./laravel-tests/aio
cp -rf ./resources ./laravel-tests/aio
cp -rf ./src ./laravel-tests/aio
cp -rf ./tests ./laravel-tests/aio
cp -rf ./composer.json ./laravel-tests/aio
rm -rf ./laravel-tests/tests
cp -rf ./tests ./laravel-tests/tests
cp -f ./phpunit.dusk.xml ./laravel-tests
cp -f ./.env.testing ./laravel-tests/.env
cd ./laravel-tests
php artisan admin:composer-config
composer require appsolutely/aio:*@dev
composer require "laravel/dusk:*" --dev # --ignore-platform-reqs
