# Installation and Configuration

The steps below will walk you through installing an Apache/MySQL/PHP environment on your Linux system, then installing and configuring the application to work in that environment. You will need `root` access to perform some of these steps.

## Linux

* Install Apache, PHP, MySQL and Git.

      apt-get install apache2 php5 php5-intl php5-mysqlnd mysql-server mysql-client

* Update your Apache configuration file.

  * Ensure that your Apache server has the `AllowOverride All` directive set for the Web server document root.

        <Directory /var/www>
          ...
          AllowOverride All
        </Directory>

  * Ensure that your Apache server has the `mod_rewrite` module enabled.

        LoadModule rewrite_module /usr/lib/apache2/modules/mod_rewrite.so

  * Ensure that the `DirectoryIndex` directive supports `.php` file extensions.

        DirectoryIndex index.php index.html

* Update your `php.ini` file.

  * Set the `date.timezone` configuration value in your `php.ini` file to reflect your local timezone. [Find your timezone in the PHP manual](http://php.net/manual/en/timezones.php).

        date.timezone=IST

  * Set the `post_max_size` and `upload_max_filesize` configuration values in your `php.ini` file to a value 25% higher than the maximum possible size of a file upload.

        post_max_size=50M
        upload_max_filesize=50M

  * Set the `file_uploads` configuration value in your `php.ini` file to `On`.

        file_uploads=On

  > If administrator access to the `php.ini` file is not available, set these values in the `/var/www/app/.htaccess` file using either the `php_flag` or `php_value` directives.

* Restart the Apache server.

      service apache2 restart

* Create an empty MySQL database for the application.

      echo "CREATE DATABASE app" | mysql -u root -p
      echo "GRANT ALL ON app.* TO 'app-user'@'localhost' IDENTIFIED BY 'app-password'" | mysql -u root -p

  > Update the previous command to use a more complex password if you wish.

* Install [Composer](http://getcomposer.org/).

      cd /usr/local/bin
      php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
      php composer-setup.php
      mv composer.phar composer

* Clone or download the [code from Github](https://github.com/vvaswani/jade/).

  > The steps below assume that your Web server document root is installed in `/var/www`. If your Web server document root is configured to use a different location, replace the paths below accordingly.

      cd /var/www
      git clone -c core.symlinks=true https://github.com/vvaswani/jade/ app
      git checkout master

* Download dependencies by executing `composer install`.

      cd /var/www/app
      composer install

* Ensure that the `/var/www/app/data/cache`, `/var/www/app/data/tmp` and `/var/www/app/data/upload` directories are writable by the Web server user.

      chown -R www-data data/tmp
      chown -R www-data data/cache
      chown -R www-data data/upload

* Copy `/var/www/app/config/autoload/local.php.dist` to `/var/www/app/config/autoload/local.php`. Any changes to this file will be ignored by Git to enable per-developer configuration.

      cp config/autoload/local.php.dist config/autoload/local.php

* Update the `doctrine.connections.orm_default.params` key in `/var/www/app/config/autoload/local.php` with the correct database credentials for the Doctrine ORM connection. Update the password as needed.

      ...
      'params' => [
          'host'     => 'localhost',
          'port'     => '3306',
          'user'     => 'app-username',
          'password' => 'app-password',
          'dbname'   => 'app',
      ]
      ...

* Update the `translator.locale` key in `/var/www/app/config/autoload/local.php` with the required locale and language (defaults to `English (UK)`, other languages may require [additional translation files](LOCALIZATION.md)).

      ...
      'translator' => [
          'locale' => 'en_GB',
      ],
      ...

* Create the database tables by running the commands below from the `/var/www/app` directory.

      ./vendor/bin/doctrine-module orm:schema-tool:create
      ./vendor/bin/doctrine-module orm:fixtures:load

* Browse to `http://localhost/app` to access the application.