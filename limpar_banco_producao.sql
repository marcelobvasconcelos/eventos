-- DESATIVAR VERIFICAÇÃO DE CHAVE ESTRANGEIRA PARA PERMITIR TRUNCATE
SET FOREIGN_KEY_CHECKS = 0;

-- LIMPAR TABELAS TRANSACIONAIS (ORDEM NÃO IMPORTA COM FK DESATIVADA, MAS BOM MANTER LOGICA)

-- 1. Empréstimos e Itens Pendentes
TRUNCATE TABLE loans;
TRUNCATE TABLE pending_items;

-- 2. Solicitações e Notificações (se existirem)
TRUNCATE TABLE event_requests; 
TRUNCATE TABLE notifications;
TRUNCATE TABLE user_tokens; -- Opcional: Limpa tokens de recuperação de senha antigos

-- 3. Eventos (Principal Tabela Transacional)
TRUNCATE TABLE events;

-- 4. Logs (se existir tabela de logs)
-- TRUNCATE TABLE logs; 

-- REATIVAR VERIFICAÇÃO DE CHAVE ESTRANGEIRA
SET FOREIGN_KEY_CHECKS = 1;

-- CONFIRMAÇÃO (APENAS PARA VISUALIZAÇÃO)
SELECT 'Limpeza concluida. Usuarios mantidos.' AS Status;
SELECT COUNT(*) AS Users_Count FROM users;
SELECT COUNT(*) AS Events_Count FROM events;
