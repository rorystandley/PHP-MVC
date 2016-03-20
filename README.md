# PHP - MVC

A very simple PHP MVC/ORM framework. 

Point your vhost at the public_html folder.

To get up and running use the below tools to help -

	1. git clone https://github.com/rorystandley/PHP-MVC.git .
	2. git remote rename origin upstream
	3. git remote add origin *URL_TO_NEW_REPO*
	4. git push origin master

To pull in patches from upstream, simply use the following - 

	git pull upstream master

## DB-Migrations

This framework intends to work with the Node Package db-migrate

	npm install db-migrate

Documentation can be found here - [db-migrate](http://umigrate.readthedocs.org/projects/db-migrate/en/latest/)

## Gulp

This framework intends to work with Gulp

	npm install -g gulp
	gulp

Documentation can be found here - [Gulp](https://github.com/gulpjs/gulp/blob/master/docs/getting-started.md)

## Dev Folder

All JS & CSS should be added here. Gulp, will add these files into the public_html folder.