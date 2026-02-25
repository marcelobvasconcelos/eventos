-- fix_production_data.sql
-- Script para corrigir inconsistências de dados comuns na produção.

-- 1. Preencher 'created_by' nulos em eventos de administradores/sistema
-- Isso evita o erro de "Acesso Negado" para itens órfãos.
-- Altere o ID '1' se o seu admin principal tiver outro ID.
UPDATE events SET created_by = 1 WHERE created_by IS NULL;
UPDATE event_requests SET user_id = 1 WHERE user_id IS NULL;

-- 2. Garantir que o ENUM de status da tabela 'events' suporte todos os estados necessários
ALTER TABLE events MODIFY COLUMN status ENUM('Pendente', 'Aprovado', 'Rejeitado', 'Concluido', 'Cancelado') NOT NULL DEFAULT 'Pendente';

-- 3. Caso a coluna 'end_date' ainda exista e esteja causando problemas no INSERT
-- Renomeamos para 'end_date_legacy' para evitar conflitos com o código novo que usa 'start_time/end_time'.
-- Se o comando abaixo falhar porque a coluna não existe, pode ignorar.
-- ALTER TABLE events CHANGE COLUMN end_date end_date_legacy DATETIME NULL;

-- 4. Corrigir status de itens de patrimônio
ALTER TABLE asset_items MODIFY COLUMN status ENUM('Disponivel', 'Emprestado') NOT NULL DEFAULT 'Disponivel';