Projet YouBetSport
================

Version 1.0

Launched : 2014 (!!!)
Symfony version : 2.8

### Installation

## Docker
On your machine, install Docker. 
We have 2 containers : one for mariadb database and one for Adminer

```bash
#### start docker containers
docker-compose up -d

#### stop the containers
docker-compose down
```

## Symfony server

For local development purpose you can use Symfony embedded web server :
Launch Symfony server :
```bash
symfony serve -d
```

Be aware that you should install Php 7.0 version on your system to use it (with some extensions). 
```bash
sudo apt install php7.0 php7.0-xml php7.0-mysql
```

Create a .php-version file at the root directory of the project and indicate the php version to use :
```bash
// .php-version file
7.0
```

Now you can install project dependencies.
First download a valid composer version, Composer version 2.2.24 is the last version working.
Then install dependencies with composer
```bash
symfony php app/console composer.phar install
```

Then install assets :
```bash
symfony php app/console assets:install
```

You can now connect to the local interface running on port 8000 :
```bash
https://localhost:8000/
```

## Fixtures
To test the application locally, you can load fixtures that will help you test UX/UI easily
```bash
symfony php app/console doctrine:fixtures:load
```

## Create admin user

Once you have some data with fixtures, create an admin user to administrate your data :
```bash
 symfony php app/console fos:user:create adminuser youremail@yourdomain.tld your_password
```
and then promote your user to the ROLE_SUPER_ADMIN :
```bash
symfony php app/console fos:user:promote adminuser
```
Type : ROLE_SUPER_ADMIN

### Adminer

Some data are not administrable via administration panel so you may want to directly administrate via db access
There's a docker container for Adminer, for mariadb administration purpose
To access Adminer :
```bash
http://localhost:8080/admin
```