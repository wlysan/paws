<?php
// Teste da função get_route - salve como test_get_route.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function test_get_route($url) {
    // Simular $_SERVER['REQUEST_URI']
    $_SERVER['REQUEST_URI'] = $url;
    
    // Incluir a função
    include 'config/functions.php';
    
    // Chamar a função
    $result = get_route();
    
    // Retornar o resultado
    return $result;
}

// Testar várias URLs
$tests = [
    '/index.php',
    '/index.php/home',
    '/index.php/admin/login',
    '/index.php/admin/dashboard',
    '/index.php/admin/login/reset',
    '/index.php/admin/products/edit/123'
];

echo "<h1>Testes da função get_route()</h1>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>URL</th><th>Resultado</th></tr>";

foreach ($tests as $url) {
    $result = test_get_route($url);
    echo "<tr>";
    echo "<td>" . htmlspecialchars($url) . "</td>";
    echo "<td><pre>" . print_r($result, true) . "</pre></td>";
    echo "</tr>";
}

echo "</table>";