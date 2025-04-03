<?php
/**
 * Debug script for diagnosing product listing issues
 * 
 * Usage: Include this file at the end of sys_product_controller.php or 
 * add ?debug_products=1 to the URL to run diagnostics
 */

// Função para imprimir informações de debug de forma legível
function debug_print($label, $data) {
    echo '<div style="margin: 10px; padding: 10px; border: 1px solid #ccc; background: #f9f9f9;">';
    echo '<h3>' . htmlspecialchars($label) . '</h3>';
    echo '<pre>';
    
    if (is_string($data)) {
        echo htmlspecialchars($data);
    } else {
        print_r($data);
    }
    
    echo '</pre>';
    echo '</div>';
}

// Início do diagnóstico
echo '<h1>Diagnóstico de Listagem de Produtos</h1>';

try {
    // Verificar conexão com o banco de dados
    echo '<h2>Verificando conexão com o banco de dados</h2>';
    $pdo = getConnection();
    echo '<p style="color: green;">✓ Conexão com o banco de dados bem-sucedida!</p>';
    
    // Verificar se a tabela products existe
    echo '<h2>Verificando tabela products</h2>';
    $stmt = $pdo->prepare("
        SHOW TABLES LIKE 'products'
    ");
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo '<p style="color: green;">✓ Tabela products existe!</p>';
        
        // Verificar estrutura da tabela
        echo '<h2>Estrutura da tabela products</h2>';
        $stmt = $pdo->prepare("DESCRIBE products");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        debug_print('Colunas da tabela products', $columns);
        
        // Verificar se há produtos
        echo '<h2>Verificando produtos</h2>';
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total 
            FROM products 
            WHERE is_deleted = 0
        ");
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($count['total'] > 0) {
            echo '<p style="color: green;">✓ Encontrados ' . $count['total'] . ' produtos!</p>';
            
            // Tentar obter o primeiro produto
            echo '<h2>Obtendo primeiro produto</h2>';
            $stmt = $pdo->prepare("
                SELECT * 
                FROM products 
                WHERE is_deleted = 0 
                LIMIT 1
            ");
            $stmt->execute();
            $first_product = $stmt->fetch(PDO::FETCH_ASSOC);
            debug_print('Primeiro produto', $first_product);
            
            // Testar a consulta completa
            echo '<h2>Testando consulta completa</h2>';
            try {
                $stmt = $pdo->prepare("
                    SELECT p.*, 
                           (SELECT pi.image_path 
                            FROM product_images pi 
                            WHERE pi.product_id = p.id AND pi.is_primary = 1 
                            LIMIT 1) as primary_image,
                           (SELECT COUNT(*) 
                            FROM product_category_relationships pcr 
                            WHERE pcr.product_id = p.id) as category_count,
                           (SELECT GROUP_CONCAT(c.name SEPARATOR ', ') 
                            FROM product_categories c 
                            JOIN product_category_relationships pcr ON c.id = pcr.category_id 
                            WHERE pcr.product_id = p.id) as category_names
                    FROM products p
                    WHERE p.is_deleted = 0
                    ORDER BY p.created_at DESC
                    LIMIT 5
                ");
                $stmt->execute();
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo '<p style="color: green;">✓ Consulta completa bem-sucedida!</p>';
                debug_print('Produtos obtidos', $products);
            } catch (PDOException $e) {
                echo '<p style="color: red;">✗ Erro na consulta completa: ' . $e->getMessage() . '</p>';
                
                // Testar cada parte da consulta separadamente
                echo '<h3>Testando partes da consulta:</h3>';
                
                // Teste 1: Consulta básica
                try {
                    $stmt = $pdo->prepare("SELECT * FROM products WHERE is_deleted = 0 LIMIT 3");
                    $stmt->execute();
                    $basic_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo '<p style="color: green;">✓ Consulta básica bem-sucedida!</p>';
                } catch (PDOException $e) {
                    echo '<p style="color: red;">✗ Erro na consulta básica: ' . $e->getMessage() . '</p>';
                }
                
                // Teste 2: Subconsulta da imagem
                try {
                    $product_id = isset($first_product['id']) ? $first_product['id'] : 1;
                    $stmt = $pdo->prepare("
                        SELECT pi.image_path 
                        FROM product_images pi 
                        WHERE pi.product_id = ? AND pi.is_primary = 1 
                        LIMIT 1
                    ");
                    $stmt->execute([$product_id]);
                    $image = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo '<p style="color: green;">✓ Subconsulta de imagem bem-sucedida!</p>';
                    debug_print('Imagem', $image);
                } catch (PDOException $e) {
                    echo '<p style="color: red;">✗ Erro na subconsulta de imagem: ' . $e->getMessage() . '</p>';
                }
                
                // Teste 3: Verificar tabela product_images
                try {
                    $stmt = $pdo->prepare("SHOW TABLES LIKE 'product_images'");
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        echo '<p style="color: green;">✓ Tabela product_images existe!</p>';
                        
                        $stmt = $pdo->prepare("DESCRIBE product_images");
                        $stmt->execute();
                        $img_columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        debug_print('Colunas da tabela product_images', $img_columns);
                    } else {
                        echo '<p style="color: red;">✗ Tabela product_images não existe!</p>';
                    }
                } catch (PDOException $e) {
                    echo '<p style="color: red;">✗ Erro ao verificar product_images: ' . $e->getMessage() . '</p>';
                }
                
                // Teste 4: Verificar tabela product_category_relationships
                try {
                    $stmt = $pdo->prepare("SHOW TABLES LIKE 'product_category_relationships'");
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        echo '<p style="color: green;">✓ Tabela product_category_relationships existe!</p>';
                        
                        $stmt = $pdo->prepare("DESCRIBE product_category_relationships");
                        $stmt->execute();
                        $rel_columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        debug_print('Colunas da tabela product_category_relationships', $rel_columns);
                    } else {
                        echo '<p style="color: red;">✗ Tabela product_category_relationships não existe!</p>';
                    }
                } catch (PDOException $e) {
                    echo '<p style="color: red;">✗ Erro ao verificar product_category_relationships: ' . $e->getMessage() . '</p>';
                }
            }
        } else {
            echo '<p style="color: orange;">⚠ Nenhum produto encontrado!</p>';
        }
    } else {
        echo '<p style="color: red;">✗ Tabela products não existe!</p>';
    }
    
} catch (PDOException $e) {
    echo '<p style="color: red;">✗ Erro de conexão com o banco de dados: ' . $e->getMessage() . '</p>';
}