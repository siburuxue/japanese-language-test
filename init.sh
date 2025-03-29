#! /bin/bash
composer install
php bin/console assets:install --symlink public
npm install
npm run dev