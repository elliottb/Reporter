#!/bin/sh
docker-machine start
docker stop reporter-php1
docker rm reporter-php1
docker build -t reporter-php . 

docker run \
	-di \
	-p 8080:80 \
	-v $(pwd)/:/var/www/html/reporter \
	-e ENVIRONMENT='development' \
	--name reporter-php1 \
	reporter-php \
	&& docker logs reporter-php1;


docker ps
docker exec -it reporter-php1 /bin/bash
