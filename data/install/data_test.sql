SET search_path = model, web, public;

INSERT INTO "user" (username, password, active, email, group_id) VALUES
('testUser', '$2a$08$cVEBAnh6MIp/KEcEoMcYAOOK9B70eeq9FVQ6pNxKJK8UBfsKQeW5ycVEBAnh6MIp/KEcEoMcYAQ', 1, 'nobody@craws.net', 2),
('testUser2', '$2a$08$cVEBAnh6MIp/KEcEoMcYAOOK9B70eeq9FVQ6pNxKJK8UBfsKQeW5ycVEBAnh6MIp/KEcEoMcYAQ', 0, 'everybody@craws.net', 2);

INSERT INTO user_settings (user_id, name, value) VALUES
((SELECT id FROM "user" WHERE username = 'a'), 'layout', 'advanced'),
((SELECT id FROM "user" WHERE username = 'a'), 'language', '1'),
((SELECT id FROM "user" WHERE username = 'a'), 'table_rows', '20'),
((SELECT id FROM "user" WHERE username = 'testUser'), 'layout', 'advanced')
;

INSERT INTO entity (id, class_id, name) VALUES
(1000, (SELECT id FROM class WHERE code='E21'), 'tActor'          ),
(1001, (SELECT id FROM class WHERE code='E33'), 'tSource'         ),
(1002, (SELECT id FROM class WHERE code='E18'), 'tObject'         ),
(1003, (SELECT id FROM class WHERE code='E53'), 'tPlace'          ),
(1004, (SELECT id FROM class WHERE code='E8' ), 'tEvent'          ),
(1005, (SELECT id FROM class WHERE code='E6' ), 'tDestruction'    ),
(1006, (SELECT id FROM class WHERE code='E74'), 'tGroup'          ),
(1007, (SELECT id FROM class WHERE code='E31'), 'tBiblio'         ),
(1008, (SELECT id FROM class WHERE code='E33'), 'tDocument2'      ),
(1009, (SELECT id FROM class WHERE code='E84'), 'tCarrier'        ),
(1010, (SELECT id FROM class WHERE code='E8' ), 'tSubEvent'       ),
(1011, (SELECT id FROM class WHERE code='E55'), 'tCustomHierarchy'),
(1012, (SELECT id FROM class WHERE code='E41'), 'tAlias'          )
;

INSERT INTO hierarchy (id, name, multiple) VALUES (1011, 'tCustomHierarchy', 1);
INSERT INTO hierarchy_form (hierarchy_id, form_id) VALUES (1011, 1);

INSERT INTO user_bookmarks (user_id, entity_id) VALUES ((SELECT id FROM web."user" WHERE username = 'a'), 1001);

INSERT INTO link (property_id, domain_id, range_id) VALUES
((SELECT id FROM property WHERE code='P53' ), (SELECT id FROM entity WHERE name='tObject'  ), (SELECT id FROM entity WHERE name='tPlace')),
((SELECT id FROM property WHERE code='P2'  ), (SELECT id FROM entity WHERE name='tObject'  ), (SELECT id FROM entity WHERE name='Settlement')),
((SELECT id FROM property WHERE code='P2'  ), (SELECT id FROM entity WHERE name='tSource'  ), (SELECT id FROM entity WHERE name='Charter')),
((SELECT id FROM property WHERE code='P67' ), (SELECT id FROM entity WHERE name='tSource'  ), (SELECT id FROM entity WHERE name='tObject')),
((SELECT id FROM property WHERE code='P128'), (SELECT id FROM entity WHERE name='tCarrier' ), (SELECT id FROM entity WHERE name='tSource')),
((SELECT id FROM property WHERE code='P117'), (SELECT id FROM entity WHERE name='tEvent'   ), (SELECT id FROM entity WHERE name='History of the World')),
((SELECT id FROM property WHERE code='P24' ), (SELECT id FROM entity WHERE name='tEvent'   ), (SELECT id FROM entity WHERE name='tObject')),
((SELECT id FROM property WHERE code='P22' ), (SELECT id FROM entity WHERE name='tEvent'   ), (SELECT id FROM entity WHERE name='tActor')),
((SELECT id FROM property WHERE code='P23' ), (SELECT id FROM entity WHERE name='tEvent'   ), (SELECT id FROM entity WHERE name='tActor')),
((SELECT id FROM property WHERE code='P117'), (SELECT id FROM entity WHERE name='tSubEvent'), (SELECT id FROM entity WHERE name='tEvent')),
((SELECT id FROM property WHERE code='P89' ), (SELECT id FROM entity WHERE name='tPlace'   ), (SELECT id FROM entity WHERE name='Austria')),
((SELECT id FROM property WHERE code='P67' ), (SELECT id FROM entity WHERE name='tBiblio'  ), (SELECT id FROM entity WHERE name='tActor')),
((SELECT id FROM property WHERE code='P67' ), (SELECT id FROM entity WHERE name='tBiblio'  ), (SELECT id FROM entity WHERE name='tSource')),
((SELECT id FROM property WHERE code='P67' ), (SELECT id FROM entity WHERE name='tBiblio'  ), (SELECT id FROM entity WHERE name='tObject')),
((SELECT id FROM property WHERE code='P67' ), (SELECT id FROM entity WHERE name='tBiblio'  ), (SELECT id FROM entity WHERE name='tEvent')),
((SELECT id FROM property WHERE code='P67' ), (SELECT id FROM entity WHERE name='tSource'  ), (SELECT id FROM entity WHERE name='tEvent')),
((SELECT id FROM property WHERE code='P7'  ), (SELECT id FROM entity WHERE name='tEvent'   ), (SELECT id FROM entity WHERE name='tPlace')),
((SELECT id FROM property WHERE code='P2'  ), (SELECT id FROM entity WHERE name='tEvent'   ), (SELECT id FROM entity WHERE name='Conflict')),
((SELECT id FROM property WHERE code='P2'  ), (SELECT id FROM entity WHERE name='tBiblio'  ), (SELECT id FROM entity WHERE name='Book')),
((SELECT id FROM property WHERE code='P2'  ), (SELECT id FROM entity WHERE name='tCarrier' ), (SELECT id FROM entity WHERE name='Information Carrier')),
((SELECT id FROM property WHERE code='OA8' ), (SELECT id FROM entity WHERE name='tCarrier' ), (SELECT id FROM entity WHERE name='tPlace')),
((SELECT id FROM property WHERE code='P1'  ), (SELECT id FROM entity WHERE name='tObject'  ), (SELECT id FROM entity WHERE name='tAlias'));

INSERT INTO gis.point (entity_id, name, type, geom) VALUES (1003, 'testPoint', 'centerpoint', '0101000020E6100000F5E0FE877672294095A93A0EC9F54740');
INSERT INTO gis.polygon (entity_id, name, type, geom) VALUES (1003, 'testShape', 'area', '0103000020E610000001000000040000000FDFFEEB8A722940AD57FADFC5F547409EDFFE7F9C7229403BF4BDFABBF54740BEE0FEA7BF7229402DF976C0C1F547400FDFFEEB8A722940AD57FADFC5F54740');