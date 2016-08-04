# db-tool

obmorgan/Phinx + doctrine/dbal + phpclitool

```shell
    # install
    git clone git@github.com:NinjaAnija/db-tool.git db-tool
    cd db-tool
    curl -sS https://getcomposer.org/installer | php
    php composer.phar install
    php composer.phar dump-autoload

    # phpclitool
    # getSchema -> Save serialized Doctrine\DBAL\Schema object as file
    ./db-tool getSchema
    # getDiff -> Get diff Doctrine\DBAL\Schema objects (saved by getSchema)
    ./db-tool getDiff
```
