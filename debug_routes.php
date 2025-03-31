<?php
// Arquivo de depuração de rotas - salve como debug_routes.php na raiz do projeto
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inclua as dependências necessárias
include 'config/functions.php';

// Obtenha informações da rota atual
$rota = get_route();

// Verifica se o arquivo de view existe
$view_path = get_view($rota['route']);
$view_exists = !empty($view_path) && file_exists($view_path);

// Verifica se o arquivo de controller existe
$controller_exists = false;
if (array_key_exists($rota['route'], $GLOBALS['routes'])) {
    $controller_path = $GLOBALS['routes'][$rota['route']]['controller'];
    $controller_exists = !empty($controller_path) && file_exists($controller_path);
}

// Verifica se o arquivo de estrutura existe
$structure_path = get_structure($rota['route']);
$structure_exists = !empty($structure_path) && file_exists($structure_path);

// Coleta dados do POST
$post_data = $_POST;

// Exibe todas as informações
echo "<h1>Informações de Depuração</h1>";
echo "<h2>Rota atual</h2>";
echo "<pre>";
print_r($rota);
echo "</pre>";

echo "<h2>Plugins carregados</h2>";
echo "<pre>";
print_r($GLOBALS['plugins_scope'] ?? 'Não definido');
echo "</pre>";

echo "<h2>Rotas definidas</h2>";
echo "<pre>";
print_r($GLOBALS['routes'] ?? 'Não definido');
echo "</pre>";

echo "<h2>Arquivos</h2>";
echo "<ul>";
echo "<li>View: " . htmlspecialchars($view_path) . " - " . ($view_exists ? "Existe" : "Não existe") . "</li>";
echo "<li>Controller: " . htmlspecialchars($controller_path ?? 'Não definido') . " - " . ($controller_exists ? "Existe" : "Não existe") . "</li>";
echo "<li>Estrutura: " . htmlspecialchars($structure_path) . " - " . ($structure_exists ? "Existe" : "Não existe") . "</li>";
echo "</ul>";

echo "<h2>Dados POST</h2>";
echo "<pre>";
print_r($post_data);
echo "</pre>";

echo "<h2>Sessão</h2>";
echo "<pre>";
print_r($_SESSION ?? 'Não definido');
echo "</pre>";

echo "<h2>Cookies</h2>";
echo "<pre>";
print_r($_COOKIE ?? 'Não definido');
echo "</pre>";

echo "<h2>Plugin Routes</h2>";
echo "<pre>";
print_r($GLOBALS['plugin_route'] ?? 'Não definido');
echo "</pre>";