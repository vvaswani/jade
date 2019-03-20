FROM php:7.3-apache
RUN apt-get update && apt-get install -y libicu-dev git zip unzip && docker-php-ext-configure intl && docker-php-ext-install intl pdo_mysql
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"    
RUN cp .docker/php/application.ini "$PHP_INI_DIR/conf.d/application.ini"    
COPY . /var/www/html/
WORKDIR /var/www/html
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN php composer.phar install --prefer-dist --no-scripts --no-dev --no-autoloader && rm -rf /root/.composer
RUN php composer.phar dump-autoload --no-scripts --no-dev --optimize
RUN rm composer-setup.php composer.phar Dockerfile*
RUN chown -R www-data:www-data /var/www/html && a2enmod rewrite
RUN service apache2 start