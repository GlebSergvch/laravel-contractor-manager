# Makefile — команда для полного билда/запуска проекта в контейнерах docker-compose
# Использование:
#   make setup         # билд, up, дождаться БД, composer/npm install, migrate, права на storage
#   make build         # собрать образы
#   make up            # поднять стек в фоне
#   make down          # остановить стек и удалить тома
#   make migrate       # выполнить миграции
#   make test          # запустить phpunit / artisan test внутри php-контейнера
#   make logs          # смотреть логи
#
# Настроить можно через переменные:
#   make POSTGRES_SERVICE=postgresql COMPOSE='docker compose' setup

# Команды/сервисы
COMPOSE ?= docker-compose -f docker-compose.yml
PHP_SERVICE ?= php
POSTGRES_SERVICE ?= postgresql
VUE_SERVICE ?= vue
NGINX_SERVICE ?= nginx

# Путь внутри контейнера PHP к приложению (как в docker-compose.yml)
APP_DIR ?= /var/www/backend

# Пользователь/группа для chown внутри контейнера (обычно www-data в php-fpm образах)
WWW_USER ?= www-data
WWW_GROUP ?= www-data

# Таймаут ожидания БД (секунд)
WAIT_DB_TIMEOUT ?= 60

.PHONY: all build up down restart rebuild logs ps shell-php shell-vue shell-postgres \
        composer-install npm-install migrate migrate-fresh seed storage-perms \
        key-generate test phpunit artisan-test wait-for-postgres setup

all: setup

####################
# lifecycle
####################
build:
	@echo "=> Building docker images..."
	$(COMPOSE) build

up:
	@echo "=> Starting containers (detached)..."
	$(COMPOSE) up -d

down:
	@echo "=> Stopping and removing containers (and anonymous volumes)..."
	$(COMPOSE) down -v

restart: down up

rebuild: down build up

ps:
	@$(COMPOSE) ps

logs:
	@$(COMPOSE) logs -f --tail=200

####################
# shells
####################
shell-php:
	@$(COMPOSE) exec $(PHP_SERVICE) bash

shell-vue:
	@$(COMPOSE) exec $(VUE_SERVICE) sh

shell-postgres:
	@$(COMPOSE) exec $(POSTGRES_SERVICE) bash

####################
# wait helpers
####################
wait-for-postgres:
	@echo "=> Waiting for postgres container to report healthy (timeout: $(WAIT_DB_TIMEOUT)s)..."
	@set -e; \
	CONTAINER_ID="$$( $(COMPOSE) ps -q $(POSTGRES_SERVICE) )"; \
	if [ -z "$$CONTAINER_ID" ]; then echo "Postgres container not found (is it up?)."; exit 1; fi; \
	i=0; \
	while [ "$$(docker inspect -f '{{.State.Health.Status}}' $$CONTAINER_ID 2>/dev/null || true)" != "healthy" ]; do \
		if [ $$i -ge $(WAIT_DB_TIMEOUT) ]; then echo; echo "Timed out waiting for postgres to be healthy."; docker inspect $$CONTAINER_ID || true; exit 1; fi; \
		printf "."; sleep 1; i=$$((i+1)); \
	done; \
	echo; echo "Postgres is healthy."

####################
# installs, deps
####################
composer-install:
	@echo "=> composer install (inside php container)..."
	$(COMPOSE) exec --user root $(PHP_SERVICE) bash -lc "cd $(APP_DIR) && composer install --no-interaction --prefer-dist --optimize-autoloader"

npm-install:
	@echo "=> npm install (inside vue container)..."
	$(COMPOSE) exec --user root $(VUE_SERVICE) sh -lc "cd /var/www/frontend && npm ci"

####################
# artisan / migrations / seeds
####################
migrate:
	@echo "=> Running migrations (inside php container)..."
	$(COMPOSE) exec $(PHP_SERVICE) php $(APP_DIR)/artisan migrate --force

migrate-fresh:
	@echo "=> migrate:fresh --seed (inside php container)..."
	$(COMPOSE) exec $(PHP_SERVICE) php $(APP_DIR)/artisan migrate:fresh --seed --force

seed:
	@echo "=> db:seed (inside php container)..."
	$(COMPOSE) exec $(PHP_SERVICE) php $(APP_DIR)/artisan db:seed --force

key-generate:
	@echo "=> php artisan key:generate (inside php container)..."
	$(COMPOSE) exec $(PHP_SERVICE) php $(APP_DIR)/artisan key:generate

####################
# filesystem permissions
####################
storage-perms:
	@echo "=> Fixing storage and bootstrap/cache permissions..."
	$(COMPOSE) exec --user root $(PHP_SERVICE) bash -lc "chown -R $(WWW_USER):$(WWW_GROUP) $(APP_DIR)/storage $(APP_DIR)/bootstrap/cache || true; chmod -R ug+rwx $(APP_DIR)/storage $(APP_DIR)/bootstrap/cache || true; echo 'OK'"

####################
# tests
####################
test: artisan-test

phpunit:
	@echo "=> Running phpunit (inside php container)..."
	$(COMPOSE) exec $(PHP_SERVICE) ./vendor/bin/phpunit --colors=always

artisan-test:
	@echo "=> Running artisan test (inside php container)..."
	$(COMPOSE) exec $(PHP_SERVICE) php $(APP_DIR)/artisan test --ansi

####################
# full setup (build -> up -> wait -> install deps -> migrate -> perms)
####################
setup: build up wait-for-postgres composer-install storage-perms migrate
	@echo "=> Setup finished. You can run 'make test' or 'make logs'."

# a CI target which does setup but also runs frontend install (if required) and tests
ci: build up wait-for-postgres composer-install npm-install storage-perms migrate phpunit

