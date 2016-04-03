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

copy the application to /var/www/your_sitename

### Database

WARNING! After importing data_web.sql you can login as admin (username: a, password: a), change account immediately!

postgis is needed, one way to add it is to uncomment "CREATE EXTENSION postgis;" on top of data/install/structure.sql

as postgres:
    $ createuser openatlas_master -P
    $ createdb -O openatlas_master openatlas_master
    $ cd data/install
    $ cat structure.sql data_web.sql data_crm.sql data_node.sql | psql -d openatlas_master -f -

optional - create database openatlas_master_test for unittests

### Configuration

adapt /application/configs/config.ini
adapt and rename /application/configs/password.example.ini to password.ini

### Apache

- use apache_config as template for a new vhost
- mkdir for error logs
    # a2ensite
    # apacha2ctl configtest
    # /etc/init.d/apache restart

### I18N

compile .po files in data/language
    $ msgfmt file.po -o file.mo

### Finishing

- replace default user and password
- remove the data/install directory
- test postgis, password reset, ...

## Database Dupms

### Structure

1) pg_dump -sc --if-exists -n crm -n gis -n log -n web openatlas_master > structure.sql
2) add "CREATE EXTENSION postgis;" and uncomment after installation for unittests

### CRM Data

pg_dump -a -n crm openatlas_master > data/install/data_crm.sql

### Web Schema

pg_dump -n web openatlas_master > /tmp/openatlas_web.sql

### CSV

COPY crm.class TO '/tmp/crm_class.csv' DELIMITER ',' CSV HEADER FORCE QUOTE *;
COPY crm.entity TO '/tmp/crm_entity.csv' DELIMITER ',' CSV HEADER FORCE QUOTE *;
COPY crm.link TO '/tmp/crm_link.csv' DELIMITER ',' CSV HEADER FORCE QUOTE *;
COPY crm.link_property TO '/tmp/crm_link_property.csv' DELIMITER ',' CSV HEADER FORCE QUOTE *;
COPY crm.property TO '/tmp/crm_property.csv' DELIMITER ',' CSV HEADER FORCE QUOTE *;
COPY gis.centerpoint TO '/tmp/gis_centerpoint.csv' DELIMITER ',' CSV HEADER FORCE QUOTE *;
