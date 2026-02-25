-- Script de atualização da tabela 'events' (Compatibilidade Universal)
-- Execute cada comando abaixo individualmente.
-- Nota: Se um comando der erro de "Duplicate column", significa que a coluna já existe. Pode ignorar e passar para o próximo.

ALTER TABLE events ADD COLUMN start_time time DEFAULT NULL;
ALTER TABLE events ADD COLUMN end_time time DEFAULT NULL;
ALTER TABLE events ADD COLUMN schedule_file_path varchar(255) DEFAULT NULL;
ALTER TABLE events ADD COLUMN external_link varchar(255) DEFAULT NULL;
ALTER TABLE events ADD COLUMN link_title varchar(100) DEFAULT NULL;
ALTER TABLE events ADD COLUMN custom_location varchar(255) DEFAULT NULL;
ALTER TABLE events ADD COLUMN public_estimation int(11) DEFAULT 0;
ALTER TABLE events ADD COLUMN requires_registration tinyint(1) DEFAULT 0;
ALTER TABLE events ADD COLUMN max_participants int(11) DEFAULT NULL;
ALTER TABLE events ADD COLUMN has_certificate tinyint(1) DEFAULT 0;
