Setup a minimal, docker-based LAMP stack for Laravel.


### Install laravel
https://laravel.com/docs/8.x/installation

### Copy files from this repo
- docker-compose-custom.yml
- docker-compose-custom-php.docker-file

### Edit .env

- set compose project name 
```
COMPOSE_PROJECT_NAME=laravel-docker-boilerplate
```

- map  containers' ports to host's ports
```
COMPOSE_WEB_PORT="8080:80"
COMPOSE_MYSQL_PORT="3000:3306"
COMPOSE_PHPMYADMIN_PORT="5000:80"
```

- set apache running user
```
COMPOSE_APACHE_RUN_USER=#1000
COMPOSE_APACHE_RUN_GROUP=#1000
```

### Start containers

- `docker-compose -f docker-compose-custom.yml up`


### Ready
Visit http://localhost:8080 (or any host's port according to COMPOSE_WEB_PORT).  
Phpmyadmin is available on http://localhost:5000  (or any host's port according to COMPOSE_PHPMYADMIN_PORT).  
Mysql data dir is mounted at ./docker-data/mysql.  
Apache logs dir is mounted on ./docker-data/log.  
### Shortcut
```
git clone https://github.com/SirFaenor/laravel-docker-boilerplate && \
composer create-project laravel/laravel example-app && \
cp laravel-docker-boilerplate/.env laravel-docker-boilerplate/docker-compose-custom.yml  laravel-docker-boilerplate/docker-compose-custom-php.dockerfile example-app && \
cd example-app  && \
docker-compose -f docker-compose-custom.yml up
```
