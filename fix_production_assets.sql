-- Execute os comandos abaixo um por um no seu banco de dados de PRODUÇÃO.
-- Nota: Se disser que coluna/tabela já existe, apenas ignore e passe para o próximo.

-- 1. Criar a tabela de categorias se não existir
CREATE TABLE IF NOT EXISTS asset_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Corrigir a tabela 'assets' (adicionar colunas se faltarem)
ALTER TABLE assets ADD COLUMN IF NOT EXISTS category_id INT NULL AFTER description;
ALTER TABLE assets ADD COLUMN IF NOT EXISTS requires_patrimony TINYINT(1) DEFAULT 0 AFTER category_id;

-- 3. Corrigir Erro de Codificação no ENUM 'status' da tabela 'asset_items'
-- Removemos o acento para evitar erros de truncamento (Data truncated)
-- No MariaDB/MySQL antigo, se IF NOT EXISTS falhar no ALTER, rode apenas o comando básico.
ALTER TABLE asset_items MODIFY COLUMN status ENUM('Disponivel', 'Emprestado') NOT NULL DEFAULT 'Disponivel';

-- 4. Adicionar a chave estrangeira (FK) na assets
ALTER TABLE assets ADD CONSTRAINT fk_asset_category 
FOREIGN KEY (category_id) REFERENCES asset_categories(id) ON DELETE SET NULL;
