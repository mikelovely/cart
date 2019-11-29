SHELL := /bin/bash

up:
	docker network create environment_cart || true
	docker-compose -f ./ops/development/docker/db.yml -p cart-database up -d
	docker-compose -f ./ops/development/docker/composer.yml -p cart-composer up --build
	docker-compose -f ./ops/development/docker/fpm.yml -p cart-fpm up -d --build
	docker-compose -f ./ops/development/docker/nginx.yml -p cart-nginx up -d --build
	@open http://127.0.0.1:8088

down:
	docker rm -f $$(docker ps -aqf "name=cart-") || true
