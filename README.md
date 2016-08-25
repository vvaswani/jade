# Jade

Jade provides a standard set of tools for lawyers to manage their cases and clients. 

## Features

 * Browser-based dashboard for client and case management
 * Electronic, searchable repository of documents and other case artifacts
 * Consolidated time recording and reporting system
 * Desktop and mobile access

## Installation and Configuration

### Prerequisites

 * PHP 5.6 or PHP 7.0
 * Apache 2.x
 * MySQL 5.x 
 * Git

### Configuration: Apache

  * Ensure that your Apache server has `.htaccess` file support.
  * Ensure that your Apache server has the `mod_rewrite` module enabled.

### Configuration: PHP

  * Set the `date.timezone` configuration value in your `php.ini` file to reflect your local timezone.
  * Set the `post_max_size` configuration value in your `php.ini` file to a value 25% higher than the maximum possible size of a file upload.
  * Set the `upload_max_filesize` configuration value in your `php.ini` file to a value 25% higher than the maximum possible size of a file upload.
  * Set the `file_uploads` configuration value in your `php.ini` file to `On`.

If administrator access to the `php.ini` file is not available, set these values in the `$APP_DIR/.htaccess` file using either the `php_flag` or `php_value` directives.

### Installation
  
  * Create an empty MySQL database for the application.
  * Install [Composer](http://getcomposer.org/).
  * Clone or download the [application from Github](https://github.com/vvaswani/jade/). The variable `$APP_DIR` refers to the directory hosting the application source code.
  * Download dependencies by executing `composer install`.  
  * Ensure that the `$APP_DIR/data/cache` and `$APP_DIR/data/tmp` directories are writable by the Web server user.
  * Copy `$APP_DIR/config/autoload/local.php.dist` to `$APP_DIR/config/autoload/local.php`. Any changes to this file will be ignored by Git to enable per-developer configuration.
  * Update `$APP_DIR/config/autoload/local.php` with the correct database credentials for the Doctrine ORM connection.
  * (For development environments, optional) Copy `$APP_DIR/config/development.config.php.dist` to `$APP_DIR/config/development.config.php`. This enables detailed exception listings and the Zend Developer Tools (ZDT) toolbar. This is not recommended for production environments.
  * Create the database tables by running the command `vendor/bin/doctrine-module orm:schema-tool:create` from the `$APP_DIR` directory.

Sample commands:

      $ echo "CREATE DATABASE jade" | mysql -u root -p
      $ cd /var/www
      $ git clone https://github.com/vvaswani/jade/
      $ cd jade
      $ composer install
      $ chown -R www-data data/tmp
      $ chown -R www-data data/cache
      $ cp config/autoload/local.php.dist config/autoload/local.php
      $ cp config/autoload/development.config.php.dist config/autoload/development.config.php
      $ ./vendor/bin/doctrine-module orm:schema-tool:create
      
## Roadmap
If you are interested in the future direction of this project, please contribute using the [issues log](https://github.com/vvaswani/jade/issues). Your feedback is appreciated.
  
## Useful Resources
 * [Project status](https://waffle.io/vvaswani/jade)
 * [User stories](https://github.com/vvaswani/jade/issues?q=is%3Aopen+is%3Aissue+label%3Astory)
