# Upgrade

* Pull the [latest application code from Github](https://github.com/vvaswani/jade/).
* Update dependencies by executing `composer install`.
* Update the database tables by running the command `vendor/bin/doctrine-module orm:schema-tool:update --force` from the `$APP_DIR` directory.
* Update the database tables by running the command `vendor/bin/doctrine-module orm:fixtures:load --append` from the `$APP_DIR` directory.

Sample commands:

      $ cd jade
      $ git pull
      $ composer install
      $ ./vendor/bin/doctrine-module orm:schema-tool:update --force
      $ ./vendor/bin/doctrine-module orm:fixtures:load --append
