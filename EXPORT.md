## Database Dumps

Some examples to extract data from database

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
COPY crm.property TO '/tmp/crm_property.csv' DELIMITER ',' CSV HEADER FORCE QUOTE *;
COPY gis.centerpoint TO '/tmp/gis_centerpoint.csv' DELIMITER ',' CSV HEADER FORCE QUOTE *;
