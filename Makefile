SHELL := /bin/bash

server-start:
	symfony serve -d
	docker-compose up -d
	symfony run -d npm run watch
	symfony run -d --watch=config,src,templates,vendor symfony console messenger:consume async -vv
.PHONY: server-start

server-stop:
	symfony server:stop
	docker-compose stop
.PHONY: server-stop