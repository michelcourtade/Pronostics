ENV = "dev"

configure:
	@if test ! -f app/config/parameters.yml; then echo "app/config/parameters.yml is missing"; exit 1; fi
	./getComposer.sh
	$(PHP_VERSION) composer.phar install
	$(PHP_VERSION) app/console doctrine:migrations:migrate -n --env=$(ENV)
	$(PHP_VERSION) app/console assets:install --symlink
	$(PHP_VERSION) app/console assetic:dump --env=$(ENV)

install:
	$(PHP_VERSION) app/console doctrine:database:create --env=$(ENV)
	$(PHP_VERSION) app/console doctrine:schema:create --env=$(ENV)
	$(PHP_VERSION) app/console doctrine:migrations:migrate -n --env=$(ENV)
	$(PHP_VERSION) app/console doctrine:fixtures:load --env=$(ENV) --no-interaction

update-dev:
	make cc
	make drop-db
	make install

update: ENV = "prod"
update: cc

drop-db:
	$(PHP_VERSION) app/console doctrine:database:drop --env=$(ENV) --force

refresh:
	$(PHP_VERSION) app/console doctrine:fixtures:load --env=$(ENV) --no-interaction
	make cc

cs:
	bin/phpcs --extensions=php -n --standard=PSR2 --report=full src

cc:
	rm -Rf app/cache/*
