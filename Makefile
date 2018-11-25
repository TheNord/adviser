docker-up:
	docker-compose up -d

docker-down:
	docker-compose down

docker-build:
	docker-compose up --build -d

test:
	docker-compose exec php-fpm vendor/bin/phpunit --color=always

perm:
	sudo chown ${USER}:${USER} bootstrap/cache -R
	sudo chown ${USER}:${USER} storage -R
	if [ -d "node_modules" ]; then sudo chown ${USER}:${USER} node_modules -R; fi
	if [ -d "public" ]; then sudo chown ${USER}:${USER} public -R; fi

migrate:
	docker-compose exec php-fpm php artisan migrate

helper-model:
	docker exec php-fpm php artisan ide-helper:models

start-seed:
	docker exec php-fpm php artisan db:seed