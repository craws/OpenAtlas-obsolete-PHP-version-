SET search_path = model;

INSERT INTO entity (class_id, name) VALUES ((SELECT id FROM class WHERE code='E7'), 'History of the World');

INSERT INTO entity (class_id, name) VALUES
((SELECT id FROM class WHERE code='E55'), 'Information Carrier'),
((SELECT id FROM class WHERE code='E55'), 'Original Document'),
((SELECT id FROM class WHERE code='E55'), 'Copy of Document');

INSERT INTO link (property_id, range_id, domain_id) VALUES
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Information Carrier'), (SELECT id FROM entity WHERE name='Original Document')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Information Carrier'), (SELECT id FROM entity WHERE name='Copy of Document'));

INSERT INTO entity (class_id, name) VALUES
((SELECT id FROM class WHERE code='E55'), 'Bibliography'),
((SELECT id FROM class WHERE code='E55'), 'Inbook'),
((SELECT id FROM class WHERE code='E55'), 'Article'),
((SELECT id FROM class WHERE code='E55'), 'Book');

INSERT INTO link (property_id, range_id, domain_id) VALUES
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Bibliography'), (SELECT id FROM entity WHERE name='Inbook')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Bibliography'), (SELECT id FROM entity WHERE name='Article')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Bibliography'), (SELECT id FROM entity WHERE name='Book'));

INSERT INTO entity (class_id, name) VALUES
((SELECT id FROM class WHERE code='E55'), 'Edition'),
((SELECT id FROM class WHERE code='E55'), 'Charter Edition'),
((SELECT id FROM class WHERE code='E55'), 'Letter Edition'),
((SELECT id FROM class WHERE code='E55'), 'Chronicle Edition');

INSERT INTO link (property_id, range_id, domain_id) VALUES
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Edition'), (SELECT id FROM entity WHERE name='Charter Edition')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Edition'), (SELECT id FROM entity WHERE name='Letter Edition')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Edition'), (SELECT id FROM entity WHERE name='Chronicle Edition'));

INSERT INTO entity (class_id, name) VALUES
((SELECT id FROM class WHERE code='E55'), 'Actor Function'),
((SELECT id FROM class WHERE code='E55'), 'Bishop'),
((SELECT id FROM class WHERE code='E55'), 'Abbot'),
((SELECT id FROM class WHERE code='E55'), 'Pope'),
((SELECT id FROM class WHERE code='E55'), 'Emperor'),
((SELECT id FROM class WHERE code='E55'), 'Count'),
((SELECT id FROM class WHERE code='E55'), 'King');

INSERT INTO link (property_id, range_id, domain_id) VALUES
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Actor Function'), (SELECT id FROM entity WHERE name='Bishop')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Actor Function'), (SELECT id FROM entity WHERE name='Abbot')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Actor Function'), (SELECT id FROM entity WHERE name='Pope')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Actor Function'), (SELECT id FROM entity WHERE name='Emperor')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Actor Function'), (SELECT id FROM entity WHERE name='Count')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Actor Function'), (SELECT id FROM entity WHERE name='King'));

INSERT INTO entity (class_id, name) VALUES
((SELECT id FROM class WHERE code='E55'), 'Involvement'),
((SELECT id FROM class WHERE code='E55'), 'Creator'),
((SELECT id FROM class WHERE code='E55'), 'Sponsor'),
((SELECT id FROM class WHERE code='E55'), 'Victim'),
((SELECT id FROM class WHERE code='E55'), 'Offender');

INSERT INTO link (property_id, range_id, domain_id) VALUES
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Involvement'), (SELECT id FROM entity WHERE name='Creator')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Involvement'), (SELECT id FROM entity WHERE name='Sponsor')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Involvement'), (SELECT id FROM entity WHERE name='Victim')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Involvement'), (SELECT id FROM entity WHERE name='Offender'));

INSERT INTO entity (class_id, name) VALUES
((SELECT id FROM class WHERE code='E55'), 'Gender'),
((SELECT id FROM class WHERE code='E55'), 'Female'),
((SELECT id FROM class WHERE code='E55'), 'Male');

INSERT INTO link (property_id, range_id, domain_id) VALUES
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Gender'), (SELECT id FROM entity WHERE name='Female')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Gender'), (SELECT id FROM entity WHERE name='Male'));

INSERT INTO entity (class_id, name) VALUES
((SELECT id FROM class WHERE code='E55'), 'Event'),
((SELECT id FROM class WHERE code='E55'), 'Change of Property'),
((SELECT id FROM class WHERE code='E55'), 'Donation'),
((SELECT id FROM class WHERE code='E55'), 'Sale'),
((SELECT id FROM class WHERE code='E55'), 'Exchange'),
((SELECT id FROM class WHERE code='E55'), 'Conflict'),
((SELECT id FROM class WHERE code='E55'), 'Battle'),
((SELECT id FROM class WHERE code='E55'), 'Raid');

INSERT INTO link (property_id, range_id, domain_id) VALUES
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Event'), (SELECT id FROM entity WHERE name='Change of Property')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Event'), (SELECT id FROM entity WHERE name='Conflict')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Change of Property'), (SELECT id FROM entity WHERE name='Donation')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Change of Property'), (SELECT id FROM entity WHERE name='Sale')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Change of Property'), (SELECT id FROM entity WHERE name='Exchange')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Conflict'), (SELECT id FROM entity WHERE name='Battle')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Conflict'), (SELECT id FROM entity WHERE name='Raid'));


INSERT INTO entity (class_id, name) VALUES
((SELECT id FROM class WHERE code='E55'), 'Linguistic object classification'),
((SELECT id FROM class WHERE code='E55'), 'Comment'),
((SELECT id FROM class WHERE code='E55'), 'Source Content'),
((SELECT id FROM class WHERE code='E55'), 'Source Original Text'),
((SELECT id FROM class WHERE code='E55'), 'Source Translation'),
((SELECT id FROM class WHERE code='E55'), 'Source Transliteration');

INSERT INTO link (property_id, range_id, domain_id) VALUES
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Linguistic object classification'), (SELECT id FROM entity WHERE name='Comment')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Linguistic object classification'), (SELECT id FROM entity WHERE name='Source Content')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Linguistic object classification'), (SELECT id FROM entity WHERE name='Source Original Text')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Linguistic object classification'), (SELECT id FROM entity WHERE name='Source Translation')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Linguistic object classification'), (SELECT id FROM entity WHERE name='Source Transliteration'));

INSERT INTO entity (class_id, name) VALUES
((SELECT id FROM class WHERE code='E55'), 'Source'),
((SELECT id FROM class WHERE code='E55'), 'Charter'),
((SELECT id FROM class WHERE code='E55'), 'Testament'),
((SELECT id FROM class WHERE code='E55'), 'Letter'),
((SELECT id FROM class WHERE code='E55'), 'Contract');

INSERT INTO link (property_id, range_id, domain_id) VALUES
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Source'), (SELECT id FROM entity WHERE name='Charter')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Source'), (SELECT id FROM entity WHERE name='Testament')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Source'), (SELECT id FROM entity WHERE name='Letter')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Source'), (SELECT id FROM entity WHERE name='Contract'));

INSERT INTO entity (class_id, name) VALUES
((SELECT id FROM class WHERE code='E55'), 'Actor Actor Relation'),
((SELECT id FROM class WHERE code='E55'), 'Kindredship'),
((SELECT id FROM class WHERE code='E55'), 'Parent of (Child of)'),
((SELECT id FROM class WHERE code='E55'), 'Social'),
((SELECT id FROM class WHERE code='E55'), 'Friend of'),
((SELECT id FROM class WHERE code='E55'), 'Enemy of'),
((SELECT id FROM class WHERE code='E55'), 'Mentor of (Student of)'),
((SELECT id FROM class WHERE code='E55'), 'Political'),
((SELECT id FROM class WHERE code='E55'), 'Ally of'),
((SELECT id FROM class WHERE code='E55'), 'Leader of (Retinue of)'),
((SELECT id FROM class WHERE code='E55'), 'Economical'),
((SELECT id FROM class WHERE code='E55'), 'Provider of (Customer of)');

INSERT INTO link (property_id, range_id, domain_id) VALUES
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Actor Actor Relation'), (SELECT id FROM entity WHERE name='Kindredship')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Actor Actor Relation'), (SELECT id FROM entity WHERE name='Social')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Actor Actor Relation'), (SELECT id FROM entity WHERE name='Political')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Actor Actor Relation'), (SELECT id FROM entity WHERE name='Economical')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Kindredship'), (SELECT id FROM entity WHERE name='Parent of (Child of)')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Social'), (SELECT id FROM entity WHERE name='Friend of')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Social'), (SELECT id FROM entity WHERE name='Enemy of')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Social'), (SELECT id FROM entity WHERE name='Mentor of (Student of)')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Political'), (SELECT id FROM entity WHERE name='Ally of')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Political'), (SELECT id FROM entity WHERE name='Leader of (Retinue of)')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Economical'), (SELECT id FROM entity WHERE name='Provider of (Customer of)'));

INSERT INTO entity (class_id, name) VALUES
((SELECT id FROM class WHERE code='E55'), 'Site'),
((SELECT id FROM class WHERE code='E55'), 'Settlement'),
((SELECT id FROM class WHERE code='E55'), 'Military Facility'),
((SELECT id FROM class WHERE code='E55'), 'Ritual Site'),
((SELECT id FROM class WHERE code='E55'), 'Burial Site'),
((SELECT id FROM class WHERE code='E55'), 'Infrastructure'),
((SELECT id FROM class WHERE code='E55'), 'Economic Site'),
((SELECT id FROM class WHERE code='E55'), 'Boundary Mark'),
((SELECT id FROM class WHERE code='E55'), 'Topographical Entity');

INSERT INTO link (property_id, range_id, domain_id) VALUES
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Site'), (SELECT id FROM entity WHERE name='Settlement')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Site'), (SELECT id FROM entity WHERE name='Military Facility')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Site'), (SELECT id FROM entity WHERE name='Ritual Site')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Site'), (SELECT id FROM entity WHERE name='Burial Site')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Site'), (SELECT id FROM entity WHERE name='Infrastructure')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Site'), (SELECT id FROM entity WHERE name='Economic Site')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Site'), (SELECT id FROM entity WHERE name='Boundary Mark')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Site'), (SELECT id FROM entity WHERE name='Topographical Entity'));

INSERT INTO entity (class_id, name) VALUES
((SELECT id FROM class WHERE code='E55'), 'Date value type'),
((SELECT id FROM class WHERE code='E55'), 'Exact date value'),
((SELECT id FROM class WHERE code='E55'), 'From date value'),
((SELECT id FROM class WHERE code='E55'), 'To date value');

INSERT INTO link (property_id, range_id, domain_id) VALUES
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Date value type'), (SELECT id FROM entity WHERE name='Exact date value')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Date value type'), (SELECT id FROM entity WHERE name='From date value')),
((SELECT id FROM property WHERE code='P127'), (SELECT id FROM entity WHERE name='Date value type'), (SELECT id FROM entity WHERE name='To date value'));

INSERT INTO entity (class_id, name) VALUES
((SELECT id FROM class WHERE code='E53'), 'Administrative Unit'),
((SELECT id FROM class WHERE code='E53'), 'Austria'),
((SELECT id FROM class WHERE code='E53'), 'Wien'),
((SELECT id FROM class WHERE code='E53'), 'Kärnten'),
((SELECT id FROM class WHERE code='E53'), 'Niederösterreich'),
((SELECT id FROM class WHERE code='E53'), 'Oberösterreich'),
((SELECT id FROM class WHERE code='E53'), 'Salzburg'),
((SELECT id FROM class WHERE code='E53'), 'Tirol'),
((SELECT id FROM class WHERE code='E53'), 'Steiermark'),
((SELECT id FROM class WHERE code='E53'), 'Vorarlberg'),
((SELECT id FROM class WHERE code='E53'), 'Burgenland'),
((SELECT id FROM class WHERE code='E53'), 'Germany'),
((SELECT id FROM class WHERE code='E53'), 'Italy'),
((SELECT id FROM class WHERE code='E53'), 'Czech Republic'),
((SELECT id FROM class WHERE code='E53'), 'Slovakia'),
((SELECT id FROM class WHERE code='E53'), 'Slovenia');

INSERT INTO link (property_id, range_id, domain_id) VALUES
((SELECT id FROM property WHERE code='P89'), (SELECT id FROM entity WHERE name='Administrative Unit'), (SELECT id FROM entity WHERE name='Austria')),
((SELECT id FROM property WHERE code='P89'), (SELECT id FROM entity WHERE name='Administrative Unit'), (SELECT id FROM entity WHERE name='Italy')),
((SELECT id FROM property WHERE code='P89'), (SELECT id FROM entity WHERE name='Administrative Unit'), (SELECT id FROM entity WHERE name='Germany')),
((SELECT id FROM property WHERE code='P89'), (SELECT id FROM entity WHERE name='Administrative Unit'), (SELECT id FROM entity WHERE name='Czech Republic')),
((SELECT id FROM property WHERE code='P89'), (SELECT id FROM entity WHERE name='Administrative Unit'), (SELECT id FROM entity WHERE name='Slovakia')),
((SELECT id FROM property WHERE code='P89'), (SELECT id FROM entity WHERE name='Administrative Unit'), (SELECT id FROM entity WHERE name='Slovenia')),
((SELECT id FROM property WHERE code='P89'), (SELECT id FROM entity WHERE name='Austria'), (SELECT id FROM entity WHERE name='Wien')),
((SELECT id FROM property WHERE code='P89'), (SELECT id FROM entity WHERE name='Austria'), (SELECT id FROM entity WHERE name='Kärnten')),
((SELECT id FROM property WHERE code='P89'), (SELECT id FROM entity WHERE name='Austria'), (SELECT id FROM entity WHERE name='Niederösterreich')),
((SELECT id FROM property WHERE code='P89'), (SELECT id FROM entity WHERE name='Austria'), (SELECT id FROM entity WHERE name='Oberösterreich')),
((SELECT id FROM property WHERE code='P89'), (SELECT id FROM entity WHERE name='Austria'), (SELECT id FROM entity WHERE name='Salzburg')),
((SELECT id FROM property WHERE code='P89'), (SELECT id FROM entity WHERE name='Austria'), (SELECT id FROM entity WHERE name='Tirol')),
((SELECT id FROM property WHERE code='P89'), (SELECT id FROM entity WHERE name='Austria'), (SELECT id FROM entity WHERE name='Steiermark')),
((SELECT id FROM property WHERE code='P89'), (SELECT id FROM entity WHERE name='Austria'), (SELECT id FROM entity WHERE name='Vorarlberg')),
((SELECT id FROM property WHERE code='P89'), (SELECT id FROM entity WHERE name='Austria'), (SELECT id FROM entity WHERE name='Burgenland'));

INSERT INTO entity (class_id, name) VALUES
((SELECT id FROM class WHERE code='E53'), 'Historical Place'),
((SELECT id FROM class WHERE code='E53'), 'Carantania'),
((SELECT id FROM class WHERE code='E53'), 'Marcha Orientalis'),
((SELECT id FROM class WHERE code='E53'), 'Comitatus Iauntal'),
((SELECT id FROM class WHERE code='E53'), 'Kingdom of Serbia');

INSERT INTO link (property_id, range_id, domain_id) VALUES
((SELECT id FROM property WHERE code='P89'), (SELECT id FROM entity WHERE name='Historical Place'), (SELECT id FROM entity WHERE name='Carantania')),
((SELECT id FROM property WHERE code='P89'), (SELECT id FROM entity WHERE name='Historical Place'), (SELECT id FROM entity WHERE name='Marcha Orientalis')),
((SELECT id FROM property WHERE code='P89'), (SELECT id FROM entity WHERE name='Historical Place'), (SELECT id FROM entity WHERE name='Comitatus Iauntal')),
((SELECT id FROM property WHERE code='P89'), (SELECT id FROM entity WHERE name='Historical Place'), (SELECT id FROM entity WHERE name='Kingdom of Serbia'));

INSERT INTO web.node (entity_id, name, multiple, system, is_extendable, is_directional) VALUES
((SELECT id FROM entity WHERE name='Original Document'), 'Original Document', 0, 1, 1, 0),
((SELECT id FROM entity WHERE name='Source'), 'Source', 0, 1, 1, 0),
((SELECT id FROM entity WHERE name='Event'), 'Event', 0, 1, 1, 0),
((SELECT id FROM entity WHERE name='Actor Actor Relation'), 'Actor Actor Relation', 0, 1, 1, 1),
((SELECT id FROM entity WHERE name='Actor Function'), 'Actor Function', 0, 1, 1, 0),
((SELECT id FROM entity WHERE name='Involvement'), 'Involvement', 0, 1, 1, 0),
((SELECT id FROM entity WHERE name='Gender'), 'Gender', 0, 1, 1, 0),
((SELECT id FROM entity WHERE name='Site'), 'Site', 0, 1, 1, 0),
((SELECT id FROM entity WHERE name='Information Carrier'), 'Information Carrier', 0, 1, 1, 0),
((SELECT id FROM entity WHERE name='Bibliography'), 'Bibliography', 0, 1, 1, 0),
((SELECT id FROM entity WHERE name='Edition'), 'Edition', 0, 1, 1, 0),
((SELECT id FROM entity WHERE name='Date value type'), 'Date value type', 0, 1, 0, 0),
((SELECT id FROM entity WHERE name='Linguistic object classification'), 'Linguistic object classification', 0, 1, 0, 0),
((SELECT id FROM entity WHERE name='Administrative Unit'), 'Administrative Unit', 1, 1, 1, 0),
((SELECT id FROM entity WHERE name='Historical Place'), 'Historical Place', 1, 1, 1, 0);

INSERT INTO web.form (name) VALUES
('Source'),
('Event'),
('Person'),
('Group'),
('Legal Body'),
('Place'),
('Bibliography'),
('Edition'),
('Information Carrier');



