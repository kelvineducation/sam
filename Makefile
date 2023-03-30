PG=docker-compose exec -T postgres
DB_DATE=$(shell date +%Y%m%d)
DB_NAME=sam_$(DB_DATE)

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

copy-heroku-db:
	$(PG) createdb -U postgres "$(DB_NAME)"
	heroku pg:backups:capture -a kelvin-sam
	heroku pg:backups:download -a kelvin-sam -o sam.pgc
	$(PG) pg_restore -F c -U postgres --no-privileges --no-owner -v -d "$(DB_NAME)" <sam.pgc
	$(PG) vacuumdb -Z -U postgres "$(DB_NAME)"
	echo "$(DB_NAME) is ready to use"
