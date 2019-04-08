# Upgrade

Users who are not using the Docker image and wish to perform a manual upgrade can do so by following the seps below:

* Pull the [latest application code from Github](https://github.com/vvaswani/jade/).
* Update dependencies by executing `composer install`.
* Update the database tables by running the command `vendor/bin/doctrine-module orm:schema-tool:update --force` from the `$APP_DIR` directory.
* Update the database tables by running the command `vendor/bin/doctrine-module orm:fixtures:load --append` from the `$APP_DIR` directory.

Sample commands:

      $ cd app
      $ git pull
      $ composer install
      $ ./vendor/bin/doctrine-module orm:schema-tool:update --force
      $ ./vendor/bin/doctrine-module orm:fixtures:load --append
