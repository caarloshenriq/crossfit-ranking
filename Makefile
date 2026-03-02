help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

up: ## Start all services
	docker compose up -d --build

down: ## Stop all services
	docker compose down -v

build: ## Rebuild app container
	docker compose build app

shell: ## Open a shell inside the app container
	docker compose exec app bash

install: ## Install PHP dependencies
	docker compose exec app composer install

test: ## Run all tests (requires db_test to be healthy)
	docker compose exec app composer test

test-unit: ## Run unit tests only (no DB required)
	docker compose exec app composer test:unit

test-integration: ## Run integration tests (SQLite in-memory)
	docker compose exec app composer test:integration

test-feature: ## Run feature tests (requires db_test container)
	docker compose exec app composer test:feature

test-coverage: ## Generate HTML coverage report
	docker compose exec app composer test:coverage

logs: ## Tail logs from all services
	docker compose logs -f

migrate: ## Re-run migrations manually
	docker compose exec db mysql -u ranking_user -psecret ranking_movimento < database/migrations/001_schema_and_seed.sql

ps: ## Show running containers
	docker compose ps
