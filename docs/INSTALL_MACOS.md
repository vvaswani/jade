# Installation and Configuration

The steps below will walk you through installing an Apache/MySQL/PHP environment on your macOS system, then installing and configuring the application to work in that environment. You will need `root` access to perform some of these steps.

## macOS

* Download, install and start MySQL.

      brew install mysql
      mysql.server start

* Download and install PHP via Homebrew.

      brew tap homebrew/homebrew-php
      brew install php71 --with-httpd
      brew install php71-intl

    > The steps below assume that PHP and Apache are installed in `/usr/local` and that the Apache document root is `/usr/local/var/www`. If you installed these components to a different location, replace the paths below accordingly.

* Download and install [Git](https://git-scm.com/download/mac). Ensure that Git is added to the system path.

* Turn off the running macOS Apache server.

      /usr/sbin/apachectl stop

* Edit the Homebrew Apache configuration file at `/usr/local/etc/httpd/httpd.conf`.

    * Set the Apache server port to 80 by adjusting the `Listen` directive.

          Listen 80

    * Find the `DirectoryIndex` directive and update it to support `.php` file extensions.

          DirectoryIndex index.php index.html

    * Find the `<Directory /usr/local/var/www>...</Directory>` block and update the `AllowOverride` directive in it to support local overrides.

          <Directory /usr/local/var/www>
            ...
            AllowOverride All
          </Directory>

    * Activate PHP and URL rewriting support by adding the following lines to the end of the file:

          LoadModule rewrite_module lib/httpd/modules/mod_rewrite.so
          LoadModule php7_module /usr/local/opt/php71/libexec/apache2/libphp7.so
          <FilesMatch .php$>
            SetHandler application/x-httpd-php
          </FilesMatch>

* Edit the `/usr/local/etc/php/7.1/php.ini` file.

    * Configure the maximum size for file uploads by adjusting the `post_max` and `upload_max` variables to a value 25% higher than the maximum possible size of a file upload.

          post_max_size=50M
          upload_max_filesize=50M

    * Set the `file_uploads` configuration value in your `php.ini` file to `On`.

          file_uploads=On

    * Configure the timezone for the application logs by adjusting the `date.timezone` variable. [Find your timezone in the PHP manual](http://php.net/manual/en/timezones.php).

          date.timezone=IST

* Create an empty MySQL database for the application.

      echo "CREATE DATABASE app" | mysql -u root
      echo "GRANT ALL ON app.* TO 'app-user'@'localhost' IDENTIFIED BY 'app-password'" | mysql -u root

  > Update the previous command to use a more complex password if you wish.

* Start the Homebrew Apache server.

      /usr/local/bin/apachectl start

* Install [Composer](https://getcomposer.org) into the `/usr/local/bin` directory.

      cd /usr/local/bin
      php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
      php composer-setup.php
      mv composer.phar composer

* Download the [latest stable release](https://github.com/vvaswani/jade/releases).
* Extract the contents of the release archive to the `/usr/local/var/www` directory.
* Rename the resulting `/usr/local/var/www/jade-x.y.z` directory to `/usr/local/var/www/app`.
* Download dependencies by executing `composer install`.

      cd /usr/local/var/www/app
      composer install

  Composer should now begin downloading all the dependencies. This process will take several minutes.

* Ensure that the `/usr/local/var/www/app/data/cache`, `/usr/local/var/www/app/data/tmp` and `/usr/local/var/www/app/data/upload` directories are writable by the Web server user.

      chown -R _www data/tmp
      chown -R _www data/cache
      chown -R _www data/upload

* Copy `/usr/local/var/www/app/config/autoload/local.php.dist` to `/usr/local/var/www/app/config/autoload/local.php`.

      cp config/autoload/local.php.dist config/autoload/local.php

* Update the `doctrine.connections.orm_default.params` key in `/usr/local/var/www/app/config/autoload/local.php` with the correct database credentials for the Doctrine ORM connection. Update the password as needed.

      ...
      'params' => [
          'host'     => 'localhost',
          'port'     => '3306',
          'user'     => 'app-user',
          'password' => 'app-password',
          'dbname'   => 'app',
      ]
      ...

* Update the `translator.locale` key in `/usr/local/var/www/app/config/autoload/local.php` with the required locale and language (defaults to `English (UK)`, other languages may require [additional translation files](LOCALIZATION.md)).

      ...
      'translator' => [
          'locale' => 'en_GB',
      ],
      ...

* Create the database tables by running the commands below from the `/usr/local/var/www/app` directory.

      ./vendor/bin/doctrine-module orm:schema-tool:create
      ./vendor/bin/doctrine-module orm:fixtures:load

* Browse to `http://localhost/app` to access the application. Log in with default username `admin@example.com` and password `admin`.

  > It is recommended that you change these credentials immediately upon login.