<?php
// debug_mode.php - Salve este arquivo na raiz do projeto

function debug_system() {
    // Verificar estrutura de rotas
    $rota = get_route();
    
    // Verificar se a view existe
    $view_path = get_view($rota['route']);
    $view_exists = !empty($view_path) && file_exists($view_path);
    
    // Verificar se o controller existe
    $controller_exists = false;
    $controller_path = '';
    if (isset($GLOBALS['routes']) && array_key_exists($rota['route'], $GLOBALS['routes'])) {
        $controller_path = $GLOBALS['routes'][$rota['route']]['controller'];
        $controller_exists = !empty($controller_path) && file_exists($controller_path);
    }
    
    // Verificar se a estrutura existe
    $structure_path = get_structure($rota['route']);
    $structure_exists = !empty($structure_path) && file_exists($structure_path);
    
    // Informações de saída
    echo "<div style='background:#f8f9fa; border:1px solid #ddd; padding:15px; margin:15px; font-family:monospace'>";
    echo "<h2>Depuração do Sistema</h2>";
    
    echo "<h3>Informações da Rota</h3>";
    echo "<pre>";
    echo "URL: " . htmlspecialchars($_SERVER['REQUEST_URI']) . "\n";
    echo "Rota detectada: " . htmlspecialchars(print_r($rota, true)) . "\n";
    echo "</pre>";
    
    echo "<h3>Arquivos</h3>";
    echo "<ul>";
    echo "<li>View: " . htmlspecialchars($view_path) . " - " . ($view_exists ? "<span style='color:green'>Existe</span>" : "<span style='color:red'>Não existe</span>") . "</li>";
    echo "<li>Controller: " . htmlspecialchars($controller_path) . " - " . ($controller_exists ? "<span style='color:green'>Existe</span>" : "<span style='color:red'>Não existe</span>") . "</li>";
    echo "<li>Estrutura: " . htmlspecialchars($structure_path) . " - " . ($structure_exists ? "<span style='color:green'>Existe</span>" : "<span style='color:red'>Não existe</span>") . "</li>";
    echo "</ul>";
    
    echo "<h3>Rotas registradas para plugins</h3>";
    echo "<pre>";
    print_r($GLOBALS['plugin_route'] ?? 'Não definido');
    echo "</pre>";
    
    echo "<h3>POST Data</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    echo "<h3>SESSION Data</h3>";
    echo "<pre>";
    print_r($_SESSION ?? 'Sessão não iniciada');
    echo "</pre>";
    
    echo "</div>";
}

// Verificar se o modo de depuração está ativado
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    // Ativar exibição de erros
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Executar depuração após o carregamento da página
    register_shutdown_function('debug_system');
}