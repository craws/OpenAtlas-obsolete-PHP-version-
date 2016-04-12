# Installation Notes

Installation with examples from a Debian 8.3 (Jessie) system.

## Requirements

### Apache 2.4

    # apt-get install apache2 libapache2-mod-php5
    # a2enmod rewrite

### PHP 5.6

    # apt-get install php5 php5-pgsql

allow shorttags in php.ini: short_open_tag=On

    # vim /etc/php5/apache2/php.ini

### PostgreSQL 9.5

add postgresql server to /etc/apt/sources.list:
deb http://apt.postgresql.org/pub/repos/apt/ jessie-pgdg main

    # apt-get install postgresql postgis postgresql-9.5-postgis-2.1

### Zend Framework 1.12

    # apt-get install zendframework

### gettext

    # apt-get install gettext

### PHPUnit 4.2.6 (optional)

    # apt-get install phpunit php5-xdebug

## Installation

### Files

copy the files to /var/www/your_sitename

### Database

WARNING! After importing data_web.sql you can login as admin (username: a, password: a), change this account immediately!

PostGis is needed. To add it uncomment "CREATE EXTENSION postgis;" on top of data/install/structure.sql

as postgres

    $ createuser openatlas_master -P
    $ createdb -O openatlas_master openatlas_master
    $ cd data/install
    $ cat structure.sql data_web.sql data_crm.sql data_node.sql | psql -d openatlas_master -f -

optional - create database openatlas_master_test for unittests

### Configuration

adapt /application/configs/config.ini

adapt and rename /application/configs/password.example.ini to password.ini

### Apache

use apache_config as template for a new vhost
mkdir for error logs

    # a2ensite
    # apacha2ctl configtest
    # /etc/init.d/apache restart

### I18N

compile .po files in data/language

    $ msgfmt file.po -o file.mo

### Finishing

- change default user and password
- remove the data/install directory
