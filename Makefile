ENV = "dev"

configure:
    @if test ! -f app/config/parameters.yml; then echo "app/config/parameters.yml is missing"; exit 1; fi
    curl -s http://getcomposer.org/installer | php
    php composer.phar install
    php app/console assets:install --symlink
    php app/console assetic:dump --env=$(ENV)

install:
    php app/console doctrine:database:create --env=$(ENV)
    php app/console doctrine:schema:create --env=$(ENV)
    php app/console doctrine:fixtures:load --env=$(ENV) --no-interaction

update-dev:
    make cc
    make drop-db
    make install

update: ENV = "prod"
update: cc

drop-db:
    php app/console doctrine:database:drop --env=$(ENV) --force

refresh:
    php app/console doctrine:fixtures:load --env=$(ENV) --no-interaction
    make cc

cs:
    bin/phpcs --extensions=php -n --standard=PSR2 --report=full src

cc:
    rm -Rf app/cache/*