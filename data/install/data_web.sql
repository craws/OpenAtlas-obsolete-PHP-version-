SET search_path = web;

ALTER SEQUENCE group_id_seq RESTART WITH 1;
ALTER SEQUENCE language_id_seq RESTART WITH 1;

INSERT INTO language (name, shortform) VALUES
('English', 'en'),
('Deutsch', 'de');

INSERT INTO "group" (name) VALUES
('admin'),
('editor'),
('manager'),
('readonly');

INSERT INTO settings ("group", name, value) VALUES
('authentication', 'failed_login_forget_minutes', '1'),
('authentication', 'failed_login_tries', '3'),
('authentication', 'random_password_length', '16'),
('authentication', 'reset_confirm_hours', '24'),
('general', 'language', '1'),
('general', 'log_level', '6'),
('general', 'maintenance', '0'),
('general', 'offline', '1'),
('general', 'sitename', 'OpenAtlas'),
('general', 'default_table_rows', '20'),
('mail', 'notify_login', '1'),
('module', 'mail', '0');

INSERT INTO "user" (username, password, active, email, group_id) VALUES
('a', '$2a$08$cVEBAnh6MIp/KEcEoMcYAOOK9B70eeq9FVQ6pNxKJK8UBfsKQeW5ycVEBAnh6MIp/KEcEoMcYAQ', 1, null, 1);

INSERT INTO content (id) VALUES (1), (2), (3), (4), (5);

INSERT INTO i18n (field, text, item_id, language_id) VALUES
('title', 'Intro', 1, 2),
('text', '<p>Intro</p>', 1, 2),
('title', 'Intro', 1, 1),
('text', '<p>Intro</p>', 1, 1),
('title', 'Kontakt', 3, 2),
('text', '<p>Kontakt</p>', 3, 2),
('title', 'Contact', 3, 1),
('text', '<p>Contact</p>', 3, 1),
('title', 'FAQ', 5, 2),
('text', '<p>FAQ</p>', 5, 2),
('title', 'FAQ', 5, 1),
('text', '<p>FAQ</p>', 5, 1);
