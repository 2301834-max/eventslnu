.PHONY: help build up down logs cache clear migrate seed tinker test

help:
	@echo "LNU System - Docker Commands"
	@echo ""
	@echo "Usage:"
	@echo "  make build          Build Docker images"
	@echo "  make up             Start all containers"
	@echo "  make down           Stop all containers"
	@echo "  make ps             List running containers"
	@echo "  make logs           View container logs"
	@echo "  make logs-app       View app container logs"
	@echo "  make logs-nginx     View nginx container logs"
	@echo "  make logs-db        View database container logs"
	@echo "  make exec           Execute shell command in app container"
	@echo "  make bash           Enter bash shell in app container"
	@echo "  make artisan        Run artisan command"
	@echo "  make migrate        Run database migrations"
	@echo "  make seed           Seed the database"
	@echo "  make cache          Clear application caches"
	@echo "  make tinker         Open Laravel Tinker REPL"
	@echo "  make test           Run tests"
	@echo "  make composer       Run composer command"
	@echo "  make npm            Run npm command"
	@echo "  make setup          Setup application (build, migrate, seed)"
	@echo "  make clean          Clean up volumes and images"

build:
	docker-compose build

up:
	docker-compose up -d
	@echo "Application is running at http://localhost"

down:
	docker-compose down

ps:
	docker-compose ps

logs:
	docker-compose logs -f

logs-app:
	docker-compose logs -f app

logs-nginx:
	docker-compose logs -f nginx

logs-db:
	docker-compose logs -f db

exec:
	docker-compose exec app bash

bash:
	docker-compose exec app bash

artisan:
	docker-compose exec app php artisan $(filter-out $@,$(MAKECMDGOALS))

migrate:
	docker-compose exec app php artisan migrate

seed:
	docker-compose exec app php artisan db:seed

cache:
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan view:clear
	docker-compose exec app php artisan route:clear

tinker:
	docker-compose exec app php artisan tinker

test:
	docker-compose exec app php artisan test

composer:
	docker-compose exec app composer $(filter-out $@,$(MAKECMDGOALS))

npm:
	docker-compose exec app npm $(filter-out $@,$(MAKECMDGOALS))

setup: build up
	@sleep 5
	docker-compose exec app cp .env.docker .env
	docker-compose exec app php artisan key:generate
	docker-compose exec app php artisan migrate --force
	docker-compose exec app npm install
	docker-compose exec app npm run build
	@echo "Setup complete!"

clean:
	docker-compose down -v
	docker system prune -f
