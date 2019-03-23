#!/bin/sh

set -e

# check for local configuration file
# create if not already present
echo "Checking for database credentials..."
if [ ! -f /var/www/html/config/autoload/local.php ]; then
	echo "No database credentials found."
	echo "Creating configuration file with database credentials..."
	cp /var/www/html/config/autoload/local.php.dist /var/www/html/config/autoload/local.php
	sed -i "s/localhost/db/g" /var/www/html/config/autoload/local.php
	sed -i "s/example-user/$MYSQL_USER/g" /var/www/html/config/autoload/local.php
	sed -i "s/example-password/$MYSQL_PASSWORD/g" /var/www/html/config/autoload/local.php
	sed -i "s/example/$MYSQL_DATABASE/g" /var/www/html/config/autoload/local.php
	echo "Configuration file created."
else
	echo "Configuration file found, skipping."
fi

# wait for database server to become active
# check for database
# create if not already present
if /var/www/html/.docker/scripts/wait-for-it.sh db:3306 ; then
	echo "Database server active."
	echo "Checking for database..."
	if ! /var/www/html/vendor/bin/doctrine-module dbal:run-sql "SELECT COUNT(*) FROM job" ; then
		echo "No database found."
		echo "Creating database schema..."
		/var/www/html/vendor/bin/doctrine-module -n orm:schema-tool:create
		echo "Loading database fixtures..."
		/var/www/html/vendor/bin/doctrine-module -n orm:fixtures:load
	else
		echo "Database found, skipping database creation."
	fi
else
	echo "Database server inactive, skipping database checks."
fi

# run default entrypoint commands for parent image
# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
        set -- apache2-foreground "$@"
fi

exec "$@"

