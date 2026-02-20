-- Execute cada comando abaixo individualmente.
-- Se algum deles der erro dizendo "Duplicate column name", significa que a coluna já existe e você pode ignorar e passar para o próximo.

-- 1. Cria a coluna 'type' se não existir
ALTER TABLE events ADD COLUMN type VARCHAR(50) DEFAULT 'evento_publico' AFTER status;

-- 2. Cria a coluna 'end_date' (usada no novo código)
ALTER TABLE events ADD COLUMN end_date DATETIME AFTER date;

-- 3. Cria as colunas que estão faltando e causando erros
ALTER TABLE events ADD COLUMN requires_registration TINYINT(1) DEFAULT 0;
ALTER TABLE events ADD COLUMN max_participants INT DEFAULT NULL;
ALTER TABLE events ADD COLUMN has_certificate TINYINT(1) DEFAULT 0;
ALTER TABLE events ADD COLUMN schedule_file_path VARCHAR(255) DEFAULT NULL;
ALTER TABLE events ADD COLUMN custom_location VARCHAR(255) DEFAULT NULL;
ALTER TABLE events ADD COLUMN public_estimation INT DEFAULT 0;
ALTER TABLE events ADD COLUMN external_link VARCHAR(255) DEFAULT NULL;
ALTER TABLE events ADD COLUMN link_title VARCHAR(255) DEFAULT NULL;
