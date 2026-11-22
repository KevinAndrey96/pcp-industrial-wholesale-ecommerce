.PHONY: help setup up down restart logs app-bash mysql-bash migrate seed fresh test lint

help:
	@echo "Available commands for PCP Industrial Wholesale Platform:"
	@echo "  make setup        - Initialize .env, build containers, run migrations & seeders"
	@echo "  make up           - Start Docker Compose services in background"
	@echo "  make down         - Stop Docker Compose services"
	@echo "  make restart      - Restart Docker Compose services"
	@echo "  make logs         - Tail Docker Compose logs"
	@echo "  make app-bash     - Open bash terminal in app container"
	@echo "  make migrate      - Run database migrations"
	@echo "  make seed         - Seed industrial catalog, price lists, and demo users"
	@echo "  make fresh        - Drop all tables, re-run migrations and seeds"
	@echo "  make lint         - Check PHP syntax across project files"
	@echo "  make test         - Run test suite"

setup:
	@if [ ! -f .env ]; then cp .env.example .env; echo "Created .env from .env.example"; fi
	docker compose build
	docker compose up -d
	docker compose exec app composer install --no-interaction --prefer-dist
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan migrate --force
	docker compose exec app php artisan db:seed --force
	@echo "PCP Platform successfully launched at http://localhost:8000"
	@echo "Mailpit UI available at http://localhost:8025"

up:
	docker compose up -d

down:
	docker compose down

restart: down up

logs:
	docker compose logs -f

app-bash:
	docker compose exec app sh

mysql-bash:
	docker compose exec mysql mysql -u pcp_user -ppcp_secret pcp_wholesale_db

migrate:
	docker compose exec app php artisan migrate --force

seed:
	docker compose exec app php artisan db:seed --force

fresh:
	docker compose exec app php artisan migrate:fresh --seed --force

lint:
	find app config routes database -name "*.php" -exec php -l {} +

test:
	docker compose exec app php artisan test
