include .env
COMPOSE := docker compose
CONSOLE := src/console

up:
	$(COMPOSE) up --build --abort-on-container-exit

down:
	$(COMPOSE) down --remove-orphans

clean:
	$(COMPOSE) down --remove-orphans --volumes --rmi all

migrate:
	$(COMPOSE) exec php php $(CONSOLE) migrate --init

rollback:
	@if [ -z "$(steps)" ]; then \
			$(COMPOSE) exec php php $(CONSOLE) migrate --rollback; \
	else \
			$(COMPOSE) exec php php $(CONSOLE) migrate --rollback $(steps); \
	fi

seed:
	@if [ -z "$(class)" ]; then \
		echo "Usage: make db/seed-specific class=YourSeederName"; \
		exit 1; \
	fi
	$(COMPOSE) exec php php $(CONSOLE) seed --class $(class)

seed-all:
	$(MAKE) db/seed class=TagsSeeder
	$(MAKE) db/seed class=PostsSeeder
	$(MAKE) db/seed class=PostTagSeeder

db/reset:
	@if [ -z "$(table)" ]; then \
    		echo "Usage: make db/fresh table=YourTableName"; \
    		exit 1; \
    	fi
	$(COMPOSE) exec mysql mysql -u$(DATABASE_USER) -p$(DATABASE_USER_PASSWORD) $(DATABASE_NAME) -e "SET FOREIGN_KEY_CHECKS=0; TRUNCATE TABLE $(table); SET FOREIGN_KEY_CHECKS=1;"

nginx/reload:
	$(COMPOSE) exec nginx nginx -s reload

php/logs:
	$(COMPOSE) exec php tail -f /var/log/php/error.log

composer/dump-autoload:
	$(COMPOSE) exec php composer dump-autoload

npm/install:
	$(COMPOSE) exec php npm install

npm/dev:
	$(COMPOSE) exec php npm run dev

npm/build:
	$(COMPOSE) exec php npm run build

cs-check:
	$(COMPOSE) exec php vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php --verbose --diff --dry-run

cs-fix:
	$(COMPOSE) exec php vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php
