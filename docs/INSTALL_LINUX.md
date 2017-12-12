# Installation and Configuration

The steps below will walk you through installing an Apache/MySQL/PHP environment on your Linux system, then installing and configuring Jade to work in that environment.

## Linux

* Install Apache, PHP, MySQL and Git.

      sudo apt-get install apache2 php5 php5-intl php5-mysqlnd mysql
      
* Ensure that your Apache server has the `AllowOverride All` directive set for the Web server document root.
* Ensure that your Apache server has the `mod_rewrite` module enabled.
* Update your `php.ini` file with the following configuration changes:
  * Set the `date.timezone` configuration value in your `php.ini` file to reflect your local timezone.

        date.timezone=IST

  * Set the `post_max_size` and `upload_max_filesize` configuration values in your `php.ini` file to a value 25% higher than the maximum possible size of a file upload.

        post_max_size=50M
        upload_max_filesize=50M
  
  * Set the `file_uploads` configuration value in your `php.ini` file to `On`.

        file_uploads=On

  > If administrator access to the `php.ini` file is not available, set these values in the `/var/www/jade/.htaccess` file using either the `php_flag` or `php_value` directives.

* Restart the Apache server.

      service apache2 restart
      
* Create an empty MySQL database for the application.

      echo "CREATE DATABASE jade" | mysql -u root -p
      echo "GRANT ALL ON jade.* TO 'jade'@'localhost' IDENTIFIED BY 'password'" | mysql -u root -p
      
  > Update the previous command to use a more complex password if you wish.

* Install [Composer](http://getcomposer.org/).
* Clone or download the [code from Github](https://github.com/vvaswani/jade/). 

  > The steps below assume that your Web server document root is installed in `/var/www`. If your Web server document root is configured to use a different location, replace the paths below accordingly.

      cd /var/www
      git clone -c core.symlinks=true https://github.com/vvaswani/jade/
      git checkout master
  
* Download dependencies by executing `composer install`.

      cd /var/www/jade
      composer install

* Ensure that the `/var/www/jade/data/cache`, `/var/www/jade/data/tmp` and `/var/www/jade/data/upload` directories are writable by the Web server user.

      chown -R www-data data/tmp
      chown -R www-data data/cache
      chown -R www-data data/upload

* Copy `/var/www/jade/config/autoload/local.php.dist` to `/var/www/jade/config/autoload/local.php`. Any changes to this file will be ignored by Git to enable per-developer configuration.

      cp config/autoload/local.php.dist config/autoload/local.php

* Update the `doctrine.connections.orm_default.params` key in `/var/www/jade/config/autoload/local.php` with the correct database credentials for the Doctrine ORM connection.

      ...
      'params' => [
          'host'     => 'localhost',
          'port'     => '3306',
          'user'     => 'jade',
          'password' => 'password',
          'dbname'   => 'jade',
      ]
      ...

* Update the `translator.locale` key in `/var/www/jade/config/autoload/local.php` with the required locale and language (defaults to `English (UK)`, other languages may require [additional translation files](LOCALIZATION.md)).

      ...
      'translator' => [
          'locale' => 'en_GB',
      ],
      ...

* (For development environments, optional) Copy `/var/www/jade/config/development.config.php.dist` to `/var/www/jade/config/development.config.php`. This enables detailed exception listings and the Zend Developer Tools (ZDT) toolbar. 

      cp config/autoload/development.config.php.dist config/autoload/development.config.php

  > This is not recommended for production environments.
  
* Create the database tables by running the commands below from the `/var/www/jade` directory.

      ./vendor/bin/doctrine-module orm:schema-tool:create
      ./vendor/bin/doctrine-module orm:fixtures:load

* Browse to `http://localhost/jade` to access the application.