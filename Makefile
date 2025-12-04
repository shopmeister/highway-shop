.PHONY: help up down restart logs shell db-shell db-import db-backup clean cache admin-pw

help: ## Show this help
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}'

up: ## Start all Docker containers
	docker-compose up -d
	@echo "✓ Services started!"
	@echo "  Frontend:    http://localhost:8088"
	@echo "  Admin:       http://localhost:8088/admin"
	@echo "  phpMyAdmin:  http://localhost:8089"
	@echo "  Mailhog:     http://localhost:8025"

down: ## Stop all Docker containers
	docker-compose down

down-volumes: ## Stop and remove all volumes (⚠️  deletes DB data!)
	docker-compose down -v

restart: down up ## Restart all services

logs: ## Show logs from all services
	docker-compose logs -f

logs-app: ## Show Shopware app logs
	docker-compose logs -f app

logs-db: ## Show database logs
	docker-compose logs -f db

shell: ## Open shell in app container
	docker exec -it highway-shop-app bash

db-shell: ## Open MySQL shell
	docker exec -it highway-shop-db mysql -uroot -proot shopware

db-import: ## Import SQL dump (usage: make db-import FILE=/path/to/dump.sql)
	@if [ -z "$(FILE)" ]; then \
		echo "❌ Usage: make db-import FILE=/path/to/dump.sql"; \
		exit 1; \
	fi
	@echo "Importing $(FILE)..."
	@if [ "$$(echo $(FILE) | grep -E '\.gz$$')" ]; then \
		gunzip < $(FILE) | docker exec -i highway-shop-db mysql -uroot -proot shopware; \
	else \
		docker exec -i highway-shop-db mysql -uroot -proot shopware < $(FILE); \
	fi
	@echo "✓ Import complete!"

db-backup: ## Create database backup
	@mkdir -p backups
	@echo "Creating backup..."
	@docker exec highway-shop-db mysqldump -uroot -proot shopware > backups/backup_$$(date +%Y%m%d_%H%M%S).sql
	@echo "✓ Backup saved to backups/"

db-fix-urls: ## Fix URLs for local development
	@echo "Fixing URLs to localhost:8088..."
	@docker exec -i highway-shop-db mysql -uroot -proot shopware -e "\
		UPDATE sales_channel_domain SET url = 'http://localhost:8088'; \
		UPDATE system_config SET configuration_value = '\"http://localhost:8088\"' WHERE configuration_key = 'core.basicInformation.shopUrl'; \
	"
	@echo "✓ URLs updated!"

admin-pw: ## Reset admin password to 'shopware'
	@echo "Resetting admin password to 'shopware'..."
	@docker exec -i highway-shop-db mysql -uroot -proot shopware -e "\
		UPDATE user SET password = '\$$2y\$$10\$$7OuQlE8AehYk/pWR4CvGP.BVCqQZC8w5Iq1P5jtdKJU8xlQMZGIAa' WHERE username = 'admin'; \
	"
	@echo "✓ Admin password reset! Login: admin / shopware"

cache: ## Clear Shopware cache
	docker exec -it highway-shop-app php bin/console cache:clear
	@echo "✓ Cache cleared!"

theme-compile: ## Compile Shopware theme
	docker exec -it highway-shop-app php bin/console theme:compile
	@echo "✓ Theme compiled!"

plugin-refresh: ## Refresh plugin list
	docker exec -it highway-shop-app php bin/console plugin:refresh
	@echo "✓ Plugins refreshed!"

clean: ## Remove all containers, volumes, and generated files
	docker-compose down -v
	rm -rf var/cache/*
	rm -rf var/log/*
	@echo "✓ Cleaned up!"

status: ## Show status of all containers
	@docker-compose ps

install: ## Fresh install (⚠️  deletes all data!)
	@echo "⚠️  This will delete all existing data!"
	@read -p "Continue? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		make down-volumes; \
		make up; \
		echo "✓ Fresh installation started. Check logs with: make logs"; \
	fi
