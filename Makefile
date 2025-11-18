.PHONY: help install up down restart logs shell test clean rebuild migrate seed fresh backup restore

# Colors for output
CYAN = \033[0;36m
GREEN = \033[0;32m
YELLOW = \033[0;33m
RED = \033[0;31m
NC = \033[0m # No Color

# Default target
.DEFAULT_GOAL := help

help: ## Show this help message
	@echo "$(CYAN)IdeaHub Docker Management$(NC)"
	@echo "$(CYAN)=========================$(NC)"
	@echo ""
	@echo "$(GREEN)Production Commands:$(NC)"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(CYAN)%-15s$(NC) %s\n", $$1, $$2}'
	@echo ""
	@echo "$(GREEN)Development Commands (use dev-* prefix):$(NC)"
	@grep -E '^dev-[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(CYAN)%-15s$(NC) %s\n", $$1, $$2}'

## Production Commands

install: ## Initial setup (create .env, build, start)
	@echo "$(GREEN)🚀 Setting up IdeaHub (Production)...$(NC)"
	@if [ ! -f .env ]; then \
		cp .env.docker.example .env; \
		echo "$(GREEN)✅ Created .env file$(NC)"; \
	else \
		echo "$(YELLOW)⚠️  .env file already exists$(NC)"; \
	fi
	@echo "$(GREEN)📦 Building Docker images...$(NC)"
	@docker-compose build
	@echo "$(GREEN)🚀 Starting services...$(NC)"
	@docker-compose up -d
	@echo "$(GREEN)⏳ Waiting for services to be ready...$(NC)"
	@sleep 10
	@echo "$(GREEN)✅ IdeaHub is ready at http://localhost:8000$(NC)"

up: ## Start all services
	@echo "$(GREEN)🚀 Starting services...$(NC)"
	@docker-compose up -d
	@echo "$(GREEN)✅ Services started$(NC)"

down: ## Stop all services
	@echo "$(YELLOW)⏹️  Stopping services...$(NC)"
	@docker-compose down
	@echo "$(GREEN)✅ Services stopped$(NC)"

restart: ## Restart all services
	@echo "$(YELLOW)🔄 Restarting services...$(NC)"
	@docker-compose restart
	@echo "$(GREEN)✅ Services restarted$(NC)"

logs: ## View logs (all services)
	@docker-compose logs -f

logs-app: ## View application logs
	@docker-compose logs -f app

logs-mysql: ## View MySQL logs
	@docker-compose logs -f mysql

logs-queue: ## View queue worker logs
	@docker-compose logs -f queue

shell: ## Access application shell
	@docker-compose exec app sh

shell-mysql: ## Access MySQL shell
	@docker-compose exec mysql mysql -uideahub -psecret ideahub

rebuild: ## Rebuild and restart all services
	@echo "$(YELLOW)🔨 Rebuilding images...$(NC)"
	@docker-compose up -d --build
	@echo "$(GREEN)✅ Rebuild complete$(NC)"

migrate: ## Run database migrations
	@echo "$(GREEN)🗄️  Running migrations...$(NC)"
	@docker-compose exec app php artisan migrate
	@echo "$(GREEN)✅ Migrations complete$(NC)"

seed: ## Seed the database
	@echo "$(GREEN)🌱 Seeding database...$(NC)"
	@docker-compose exec app php artisan db:seed
	@echo "$(GREEN)✅ Database seeded$(NC)"

fresh: ## Fresh migration with seeding (⚠️  DELETES DATA)
	@echo "$(RED)⚠️  WARNING: This will delete all data!$(NC)"
	@echo "$(RED)Press Ctrl+C to cancel, or Enter to continue...$(NC)"
	@read
	@docker-compose exec app php artisan migrate:fresh --seed
	@echo "$(GREEN)✅ Database reset complete$(NC)"

test: ## Run tests
	@echo "$(GREEN)🧪 Running tests...$(NC)"
	@docker-compose exec app php artisan test

cache-clear: ## Clear all caches
	@echo "$(GREEN)🧹 Clearing caches...$(NC)"
	@docker-compose exec app php artisan cache:clear
	@docker-compose exec app php artisan config:clear
	@docker-compose exec app php artisan route:clear
	@docker-compose exec app php artisan view:clear
	@echo "$(GREEN)✅ Caches cleared$(NC)"

optimize: ## Optimize application (cache configs)
	@echo "$(GREEN)⚡ Optimizing application...$(NC)"
	@docker-compose exec app php artisan config:cache
	@docker-compose exec app php artisan route:cache
	@docker-compose exec app php artisan view:cache
	@echo "$(GREEN)✅ Optimization complete$(NC)"

backup: ## Backup MySQL database
	@echo "$(GREEN)💾 Creating backup...$(NC)"
	@docker-compose exec mysql mysqldump -uideahub -psecret ideahub > backup_$(shell date +%Y%m%d_%H%M%S).sql
	@echo "$(GREEN)✅ Backup created$(NC)"

restore: ## Restore MySQL database from backup.sql
	@if [ ! -f backup.sql ]; then \
		echo "$(RED)❌ backup.sql not found$(NC)"; \
		exit 1; \
	fi
	@echo "$(YELLOW)📥 Restoring database...$(NC)"
	@docker-compose exec -T mysql mysql -uideahub -psecret ideahub < backup.sql
	@echo "$(GREEN)✅ Database restored$(NC)"

clean: ## Stop and remove all containers, volumes, and images (⚠️  DELETES DATA)
	@echo "$(RED)⚠️  WARNING: This will delete all data and images!$(NC)"
	@echo "$(RED)Press Ctrl+C to cancel, or Enter to continue...$(NC)"
	@read
	@docker-compose down -v --rmi all
	@echo "$(GREEN)✅ Cleanup complete$(NC)"

ps: ## Show running containers
	@docker-compose ps

stats: ## Show container resource usage
	@docker stats ideahub_app ideahub_mysql ideahub_redis ideahub_queue

## Development Commands

dev-install: ## Initial setup (development mode)
	@echo "$(GREEN)🚀 Setting up IdeaHub (Development)...$(NC)"
	@echo "$(GREEN)📦 Building Docker images...$(NC)"
	@docker-compose -f docker-compose.dev.yml build
	@echo "$(GREEN)🚀 Starting services...$(NC)"
	@docker-compose -f docker-compose.dev.yml up -d
	@echo "$(GREEN)⏳ Waiting for services to be ready...$(NC)"
	@sleep 5
	@echo "$(GREEN)✅ IdeaHub is ready!$(NC)"
	@echo "$(CYAN)  Backend:  http://localhost:8000$(NC)"
	@echo "$(CYAN)  Frontend: http://localhost:5173$(NC)"
	@echo "$(CYAN)  MailHog:  http://localhost:8025$(NC)"

dev-up: ## Start development services
	@echo "$(GREEN)🚀 Starting development services...$(NC)"
	@docker-compose -f docker-compose.dev.yml up -d
	@echo "$(GREEN)✅ Services started$(NC)"

dev-down: ## Stop development services
	@echo "$(YELLOW)⏹️  Stopping development services...$(NC)"
	@docker-compose -f docker-compose.dev.yml down
	@echo "$(GREEN)✅ Services stopped$(NC)"

dev-logs: ## View development logs
	@docker-compose -f docker-compose.dev.yml logs -f

dev-logs-backend: ## View backend logs (dev)
	@docker-compose -f docker-compose.dev.yml logs -f backend

dev-logs-frontend: ## View frontend logs (dev)
	@docker-compose -f docker-compose.dev.yml logs -f frontend

dev-shell: ## Access backend shell (dev)
	@docker-compose -f docker-compose.dev.yml exec backend sh

dev-shell-frontend: ## Access frontend shell (dev)
	@docker-compose -f docker-compose.dev.yml exec frontend sh

dev-rebuild: ## Rebuild development services
	@echo "$(YELLOW)🔨 Rebuilding development images...$(NC)"
	@docker-compose -f docker-compose.dev.yml up -d --build
	@echo "$(GREEN)✅ Rebuild complete$(NC)"

dev-test: ## Run tests (development)
	@echo "$(GREEN)🧪 Running tests...$(NC)"
	@docker-compose -f docker-compose.dev.yml exec backend php artisan test

dev-clean: ## Clean development environment
	@echo "$(RED)🧹 Cleaning development environment...$(NC)"
	@docker-compose -f docker-compose.dev.yml down -v
	@echo "$(GREEN)✅ Cleanup complete$(NC)"

## Utility Commands

composer: ## Run composer command (usage: make composer CMD="require package")
	@docker-compose exec app composer $(CMD)

artisan: ## Run artisan command (usage: make artisan CMD="make:model Post")
	@docker-compose exec app php artisan $(CMD)

npm: ## Run npm command in frontend (usage: make npm CMD="install axios")
	@docker-compose -f docker-compose.dev.yml exec frontend npm $(CMD)

health: ## Check application health
	@echo "$(GREEN)🏥 Checking application health...$(NC)"
	@curl -f http://localhost:8000/api/health && echo "$(GREEN)✅ Application is healthy$(NC)" || echo "$(RED)❌ Application is unhealthy$(NC)"
