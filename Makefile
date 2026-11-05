PHP = docker compose exec -u $(shell id -u):$(shell id -g) php php

# make console ARGS="make:entity"
console:
	$(PHP) bin/console $(ARGS)
