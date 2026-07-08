.PHONY: build up down setup migrate seed test pint sh

## Build the app image
build:
	docker compose build

## Start all services in the background
up:
	docker compose up -d

## Stop and remove containers
down:
	docker compose down

## First-time setup inside the app container
setup:
	docker compose exec app composer install
	docker compose exec app cp -n .env.example .env || true
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan migrate --seed

## Run migrations with seed data
migrate:
	docker compose exec app php artisan migrate --seed

## Run the test suite (SQLite in-memory)
test:
	docker compose exec app php artisan test

## Check code style with Pint
pint:
	docker compose exec app ./vendor/bin/pint --test

## Format code with Pint
pint-fix:
	docker compose exec app ./vendor/bin/pint

## Open a shell in the app container
sh:
	docker compose exec app sh
