THIS_FILE := $(abspath $(lastword $(MAKEFILE_LIST)))
CURRENT_DIR := $(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))

compose = docker-compose -f ./.dev/docker-compose.yml

install:
	@cp -n ./.dev/.env.example ./.dev/.env
	make up

up:
	$(compose) up --build --no-deps --detach --remove-orphans

start: up

down:
	$(compose) down  --remove-orphans

stop:
	$(compose) stop

restart: down up
	$(info Restart completed)

upgrade: ## Upgrade docker containers
	make down
	git pull origin master
	$(compose) pull
	make up
	@echo "Upgrade process finished"
	@echo "!! Please make sure that your .env file reflects needed variables from .env.example"

destroy: ## Destroy containers/volumes (keep sources app folders)
	make stop
	$(compose) down --rmi all --remove-orphans

rebuild: ## Rebuild docker container (destroy & upgrade)
	make destroy
	make upgrade

ps:
	$(compose) ps

state:
	docker ps --format=table

logs: ## Show docker logs
	$(compose) logs -f --tail=100 $(ARGS)

php:
	$(compose) exec -it toolforge bash

php-root:
	$(compose) exec -it -u root toolforge bash