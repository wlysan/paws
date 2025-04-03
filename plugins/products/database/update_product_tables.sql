-- Script para atualizar a estrutura da tabela product_categories
-- Adiciona suporte para atributos dinâmicos (serializados)

-- Verifica se a tabela product_categories já existe
SET @table_exists = 0;
SELECT COUNT(*) INTO @table_exists FROM information_schema.tables 
WHERE table_schema = DATABASE() AND table_name = 'product_categories';

-- Se a tabela existir, adiciona a coluna attributes se não existir
SET @column_exists = 0;
SELECT COUNT(*) INTO @column_exists FROM information_schema.columns 
WHERE table_schema = DATABASE() AND table_name = 'product_categories' AND column_name = 'attributes';

-- Adiciona a coluna attributes se ela não existir
SET @sql = IF(@table_exists > 0 AND @column_exists = 0,
    'ALTER TABLE product_categories ADD COLUMN attributes TEXT COMMENT "Armazena atributos dinâmicos como JSON/serializado" AFTER display_order',
    'SELECT "Column attributes already exists or table does not exist"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Garante que a estrutura da tabela tenha os campos necessários para as variações de categorias
-- Esta é uma operação segura que só cria a tabela se ela não existir
CREATE TABLE IF NOT EXISTS product_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    parent_id INT NULL,
    image_path VARCHAR(255),
    display_order INT DEFAULT 0,
    attributes TEXT COMMENT 'Armazena atributos dinâmicos como JSON/serializado',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive', 'featured') NOT NULL DEFAULT 'active',
    is_deleted BOOLEAN DEFAULT FALSE,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (parent_id) REFERENCES product_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adiciona índices para melhorar performance
CREATE INDEX IF NOT EXISTS idx_product_categories_parent_id ON product_categories(parent_id);
CREATE INDEX IF NOT EXISTS idx_product_categories_slug ON product_categories(slug);
CREATE INDEX IF NOT EXISTS idx_product_categories_status ON product_categories(status);
CREATE INDEX IF NOT EXISTS idx_product_categories_is_deleted ON product_categories(is_deleted);