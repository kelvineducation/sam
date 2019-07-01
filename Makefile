install: init migrate

init:
	docker-compose build
	docker-compose up -d
	docker-compose exec php composer install
	docker-compose exec php \
		/usr/local/bin/wait-for postgres:5432 -t 30 \
		-- bin/the migrate:setup

migrate:
	docker-compose exec php bin/the migrate
