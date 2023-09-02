# Installation and Configuration

The steps below will walk you through installing and configuring the application to work with an existing Apache/MySQL/PHP environment. You will need `root` access to perform some of these steps.

## Configuration: Apache

* Ensure that your Apache server has the `AllowOverride All` directive set for the Web server document root.
* Ensure that your Apache server has the `mod_rewrite` module enabled.

## Configuration: PHP

* Set the `date.timezone` configuration value in your `php.ini` file to reflect your local timezone.
* Set the `post_max_size` configuration value in your `php.ini` file to a value 25% higher than the maximum possible size of a file upload.
* Set the `upload_max_filesize` configuration value in your `php.ini` file to a value 25% higher than the maximum possible size of a file upload.
* Set the `file_uploads` configuration value in your `php.ini` file to `On`.

If administrator access to the `php.ini` file is not available, set these values in the `$APP_DIR/.htaccess` file using either the `php_flag` or `php_value` directives.

## Installation

* Create an empty MySQL database for the application.
* Install [Composer](http://getcomposer.org/) into your system path.
* Download the [latest application release from Github](https://github.com/vvaswani/jade/releases) and extract it into a directory under your Web server document root. The variable `$APP_DIR` refers to the directory hosting the application source code.
* Download dependencies by executing `composer install`.
* Ensure that the `$APP_DIR/data/cache`, `$APP_DIR/data/tmp` and `$APP_DIR/data/upload` directories are writable by the Web server user.
* Copy `$APP_DIR/config/autoload/local.php.dist` to `$APP_DIR/config/autoload/local.php`. Any changes to this file will be ignored by Git to enable per-developer configuration.
* Update the `doctrine.connections.orm_default.params` key in `$APP_DIR/config/autoload/local.php` with the correct database credentials for the Doctrine ORM connection.
* Update the `translator.locale` key in `$APP_DIR/config/autoload/local.php` with the required locale and language (defaults to `English (UK)`, other languages may require [additional translation files](docs/LOCALIZATION.md)).
* (For development environments, optional) Copy `$APP_DIR/config/development.config.php.dist` to `$APP_DIR/config/development.config.php`. This enables detailed exception listings and the Zend Developer Tools (ZDT) toolbar. This is not recommended for production environments.
* Create the database tables by running the command `vendor/bin/doctrine-module orm:schema-tool:create` from the `$APP_DIR` directory.
* Seed the database tables by running the command `vendor/bin/doctrine-module orm:fixtures:load` from the `$APP_DIR` directory.

Sample commands on Linux:

    echo "CREATE DATABASE app" | mysql -u root -p
    echo "GRANT ALL ON app.* TO 'app-user'@'localhost' IDENTIFIED BY 'app-password'" | mysql -u root -p
    cd /var/www
    wget https://github.com/vvaswani/jade/archive/jade-x.y.z.tar.gz
    tar -xzvf jade-x.y.z.tar.gz
    mv jade-x.y.z app
    cd app
    composer install
    chown -R www-data data/tmp
    chown -R www-data data/cache
    chown -R www-data data/upload
    cp config/autoload/local.php.dist config/autoload/local.php
    vi config/autoload/local.php
    cp config/autoload/development.config.php.dist config/autoload/development.config.php
    ./vendor/bin/doctrine-module orm:schema-tool:create
    ./vendor/bin/doctrine-module orm:fixtures:load

## Credentials

By default, the system is initialized with a single administrator account. The default username is `admin@example.com` and the default password  is `admin`. It is recommended that you change these credentials immediately upon login.