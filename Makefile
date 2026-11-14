env = local
PHP = docker compose exec -u $(shell id -u):$(shell id -g) php

placeholder:
	@echo "-----------------------------------------------------------------------------------------"
	@echo "| COMMAND                                     | DESCRIPTION                             |"
	@echo "-----------------------------------------------------------------------------------------"
	@echo "| install                                     | install and start all services          |"
	@echo "| deploy                                      | pull and istall all services            |"
	@echo "| cc                                          | cache clear                             |"
	@echo "| mig                                         | migrations migrate                      |"
	@echo "| console [command:command]                   | run 'php bin/console command:command'   |"
	@echo "| composer install                            | install composer or install dependency  |"
	@echo "| composer require [name/name]                | run composer install dependency         |"
	@echo "| test                                        | start all tests                         |"
	@echo "| test tests/Controller/SomeTest.php          | start someone test                      |"
	@echo "| ------------------------------------ | ---------------------------------------------- |"

%:
	@:

install:
	docker compose up -d --build
	@echo "Waiting for containers to be ready..."
	sleep 3
	$(PHP) composer install
	$(PHP) php bin/console doctrine:database:create --if-not-exists
	$(PHP) php bin/console doctrine:migrations:migrate --no-interaction
	$(PHP) php bin/console doctrine:database:create --env=test --if-not-exists
	$(PHP) php bin/console doctrine:migrations:migrate --env=test --no-interaction
	$(PHP) bin/console cache:clear
	@echo "Installation completed successfully!"

deploy:
	git pull origin main
	docker compose up -d --build
	@echo "Waiting for database..."
	sleep 5
	$(PHP) composer install --no-dev --optimize-autoloader
	$(PHP) php bin/console doctrine:migrations:migrate --no-interaction
	$(PHP) php bin/console cache:clear
	@echo "Deployment to production finished successfully!"

cc:
	$(PHP) php bin/console cache:clear

mig:
	$(PHP) php bin/console doctrine:migrations:migrate --no-interaction

console:
	$(PHP) php bin/console $(filter-out $@,$(MAKECMDGOALS))

composer:
	$(PHP) composer $(filter-out $@,$(MAKECMDGOALS))

test:
	$(PHP) php bin/phpunit $(filter-out $@,$(MAKECMDGOALS))

test-spec:
	@read -p "Вкажіть шлях до тесту: " t; \
	$(PHP) php bin/phpunit $$t
