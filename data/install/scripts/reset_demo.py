#!/usr/bin/python3

import psycopg2

statement = "DELETE FROM model.entity e USING web.user_log ul, web.user u WHERE e.id = ul.table_id AND ul.user_id = u.id AND u.username = 'Demolita';"

conn = psycopg2.connect("dbname=openatlas_demo user=postgres")
cur = conn.cursor()
cur.execute(statement)
conn.commit()
cur.close()
conn.close()
