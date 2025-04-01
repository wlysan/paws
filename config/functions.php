<?php

$plugin_hook = array();

/**
 * Parses the current URL and returns an associative array containing the route, controller, and action.
 *
 * This function analyzes the current URL, looking for segments that follow 'index.php'. It constructs 
 * and returns an array with 'route', 'controller', and 'action' based on the URL structure. If 'index.php' 
 * is not present in the URL, the function redirects to '/index.php'.
 *
 * @return array An associative array with 'route', 'controller', and 'action', or redirects if 'index.php' is missing.
 */

/**
 * Optimized routing function to replace the existing get_route function
 * 
 * @return array An associative array with 'route', 'controller', and 'action'
 */
function get_route() {
    $url = $_SERVER['REQUEST_URI'];
    
    // Default route
    $retorno = ['route' => '/home', 'controller' => null, 'action' => null, 'params' => null];
    
    // Check if the URL contains /index.php
    if (strpos($url, '/index.php') !== false) {
        // Extract the path after /index.php, removing any query string
        $parts = explode('/index.php', $url, 2);
        
        if (isset($parts[1]) && !empty($parts[1])) {
            // Remove any query string from the path
            $path = $parts[1];
            if (strpos($path, '?') !== false) {
                $path = substr($path, 0, strpos($path, '?'));
            }
            
            $path_parts = explode('/', trim($path, '/'));
            
            // Verificar se temos pelo menos um segmento
            if (count($path_parts) >= 1) {
                // Se temos dois ou mais segmentos
                if (isset($path_parts[1]) && !empty($path_parts[1])) {
                    // Os dois primeiros segmentos formam a rota base
                    $base_route = '/' . $path_parts[0] . '/' . $path_parts[1];
                    
                    // Se temos três ou mais segmentos, o terceiro é parte da rota ou é uma ação
                    if (isset($path_parts[2]) && !empty($path_parts[2])) {
                        // Verificar se existe rota para os três segmentos
                        $three_segment_route = $base_route . '/' . $path_parts[2];
                        
                        // Verificar se essa rota de três segmentos existe nas rotas registradas
                        global $routes, $plugin_route;
                        $route_exists = false;
                        
                        // Verificar em rotas normais
                        if (isset($routes[$three_segment_route])) {
                            $route_exists = true;
                        }
                        
                        // Verificar em rotas de plugins
                        if (!$route_exists) {
                            foreach ($plugin_route as $plugin => $plugin_routes) {
                                if (isset($plugin_routes[$three_segment_route])) {
                                    $route_exists = true;
                                    break;
                                }
                            }
                        }
                        
                        if ($route_exists) {
                            // Se a rota de três segmentos existe, use-a
                            $retorno['route'] = $three_segment_route;
                            
                            // Parâmetros começam do quarto segmento
                            $params = array_slice($path_parts, 3);
                        } else {
                            // Caso contrário, use a rota de dois segmentos e considere o terceiro como ação
                            $retorno['route'] = $base_route;
                            $retorno['action'] = $path_parts[2];
                            
                            // Parâmetros começam do quarto segmento
                            $params = array_slice($path_parts, 3);
                        }
                        
                        // Processar parâmetros se existirem
                        if (!empty($params)) {
                            $retorno['params'] = implode('|', $params);
                        }
                    } else {
                        // Apenas dois segmentos na URL
                        $retorno['route'] = $base_route;
                    }
                } else {
                    // Apenas um segmento
                    $retorno['route'] = '/' . $path_parts[0];
                }
            }
        }
        
        return $retorno;
    } else {
        // Redirect to /index.php if it's not present in the URL
        header('Location: /index.php');
        exit;
    }
}
/**
 * Retrieves the view path associated with a given route.
 *
 * This function looks up a provided view name in a global routes array and returns the associated view path.
 * If the view is not found in the routes array, the function returns null.
 *
 * @param string $view The name of the view to look up.
 * @return string|null The path to the view file or null if not found.
 */
function get_view($route) {
    global $routes;
    global $plugin_route;
    
    // Verificar se a rota existe nas rotas normais
    if (isset($routes[$route])) {
        return $routes[$route]['view'];
    }
    
    // Verificar se a rota existe nas rotas de plugins
    foreach ($plugin_route as $plugin => $plugin_routes) {
        if (isset($plugin_routes[$route])) {
            // Aqui está o arquivo da view
            $view_file = $plugin_routes[$route]['view'];
            
            // Verificação de debug
            error_log("Encontrada view para a rota $route: $view_file (plugin: $plugin)");
            
            // Verificar se o arquivo existe fisicamente
            if (file_exists($view_file)) {
                return $view_file;
            } else {
                error_log("Arquivo de view não encontrado: $view_file");
                // Continue procurando em outros plugins mesmo se o arquivo não existir
            }
        }
    }
    
    return null;
}

function get_std_controller($view) {
    global $routes;
    global $plugin_route;
    global $view_act;
    global $action;
    global $pdo;

    $view_act = $view;
    $controller_vars = [];

    // Verificar nas rotas normais
    if (array_key_exists($view, $routes)) {
        $controller_add = $routes[$view]['controller'];
        if (isset($controller_add) && $controller_add != '') {
            if (file_exists($controller_add)) {
                // Capturar variáveis do controller
                ob_start();
                include $controller_add;
                ob_end_clean();
                
                // Capturar todas as variáveis definidas
                $controller_vars = get_defined_vars();
                
                // Tornar as variáveis globais para que a view possa acessá-las
                foreach ($controller_vars as $var_name => $var_value) {
                    if ($var_name != 'routes' && $var_name != 'plugin_route' && 
                        $var_name != 'view_act' && $var_name != 'action' && 
                        $var_name != 'pdo' && $var_name != 'view' && 
                        $var_name != 'controller_add') {
                        $GLOBALS[$var_name] = $var_value;
                    }
                }
            }
        }
    }
    
    // Verificar nas rotas de plugins
    foreach ($plugin_route as $plugin => $plugin_routes) {
        if (array_key_exists($view, $plugin_routes)) {
            $controller_add = $plugin_routes[$view]['controller'];
            if (isset($controller_add) && $controller_add != '') {
                if (file_exists($controller_add)) {
                    // Capturar variáveis do controller
                    ob_start();
                    include $controller_add;
                    ob_end_clean();
                    
                    // Capturar todas as variáveis definidas
                    $controller_vars = get_defined_vars();
                    
                    // Tornar as variáveis globais para que a view possa acessá-las
                    foreach ($controller_vars as $var_name => $var_value) {
                        if ($var_name != 'routes' && $var_name != 'plugin_route' && 
                            $var_name != 'view_act' && $var_name != 'action' && 
                            $var_name != 'pdo' && $var_name != 'view' && 
                            $var_name != 'controller_add' && $var_name != 'plugin' && 
                            $var_name != 'plugin_routes') {
                            $GLOBALS[$var_name] = $var_value;
                        }
                    }
                    
                    break;
                }
            }
        }
    }

    return $controller_vars;
}
function get_controller_pview()
{
    global $plugin_location;
    global $action;
    global $pdo;

    $rota = get_route();
    $plugin = getPluginName(get_view($rota['route']));

    if (isset($rota['controller']) && $rota['controller'] != '') {
        $url = $plugin_location[$plugin] . 'controllers/' . $rota['controller'] . '_controller.php';

        if (file_exists($url)) {
            include $url;
        }
    }
}

function get_action() {
    $rota = get_route();

    // Retorna a ação se existir
    if (isset($rota['action']) && $rota['action'] != '') {
        return $rota['action'];
    }

    return null;
}

/**
 * Obtém parâmetros da URL
 * 
 * Para URLs no formato /index.php/admin/categories/edit/id/1
 * retorna um array com ['id' => '1']
 * 
 * @return array Parâmetros da URL
 */
function get_parameters() {
    $rota = get_route();
    $parameters = [];
    
    if (isset($rota['params']) && !empty($rota['params'])) {
        $params_array = explode('|', $rota['params']);
        
        // Processar pares chave/valor
        for ($i = 0; $i < count($params_array); $i += 2) {
            if (isset($params_array[$i+1])) {
                $parameters[$params_array[$i]] = $params_array[$i+1];
            }
        }
    }
    
    return $parameters;
}

function getPluginName($path)
{
    // Verifica se a string começa com "plugins/"
    if (strpos($path, 'plugins/') === 0) {
        $segments = explode('/', $path);
        if (isset($segments[1])) {
            return $segments[1];
        }
    }
    return null; // Retorna null se a string não começar com "plugins/" ou se o segundo segmento não existir
}

/**
 * Retrieves the structure file associated with a given view.
 *
 * This function looks up a provided view name in a global routes array to find its associated structure file.
 * If no structure is defined for the view, it defaults to a global core structure. It returns the path to the 
 * structure file or the default structure if none is associated with the view.
 *
 * @param string $view The name of the view for which to find the structure file.
 * @return string The path to the structure file or the default structure if none is associated.
 */
function get_structure($route) {
    global $routes;
    global $plugin_route;
    global $core_structure;
    
    $structure = null;
    
    // Verificar se a rota existe nas rotas normais
    if (isset($routes[$route])) {
        $structure = isset($routes[$route]['structure']) ? $routes[$route]['structure'] : '';
    } else {
        // Verificar se a rota existe nas rotas de plugins
        foreach ($plugin_route as $plugin => $plugin_routes) {
            if (isset($plugin_routes[$route])) {
                $structure = isset($plugin_routes[$route]['structure']) ? $plugin_routes[$route]['structure'] : '';
                
                // Verificação de debug
                error_log("Encontrada estrutura para a rota $route: $structure (plugin: $plugin)");
                
                // Verificar se o arquivo existe fisicamente
                if (!empty($structure) && file_exists($structure)) {
                    return $structure;
                } else if (!empty($structure)) {
                    error_log("Arquivo de estrutura não encontrado: $structure");
                    // Continue procurando em outros plugins
                }
            }
        }
    }
    
    // Se a estrutura ainda estiver vazia, use a estrutura padrão
    if (empty($structure)) {
        $structure = $core_structure;
    }
    
    return $structure;
}

/**
 * Scans the 'plugins/' directory and returns an array of its subdirectories as plugin names.
 *
 * @return array An array containing the names of the subdirectories within the 'plugins/' directory.
 */
function load_plugins()
{
    $directory = 'plugins/';
    $plugins = [];
    
    // Check if the directory exists
    if (!is_dir($directory)) {
        return $plugins;
    }
    
    // Get the list of files and directories
    $filesAndFolders = scandir($directory);

    foreach ($filesAndFolders as $item) {
        // Construct the full path
        $fullPath = $directory . $item;

        // Skip '.' and '..' to avoid infinite loop and skip files
        if ($item == '.' || $item == '..')
            continue;

        // Check if it's a directory and not a file
        if (is_dir($fullPath)) {
            // Print the folder name
            $plugins[] = $item;
        }
    }
    return $plugins;
}

/**
 * Echoes 'active' if the current route matches the specified route.
 *
 * This function is useful for highlighting the active route in UI elements like menus.
 *
 * @param string $route The route to check against the current route.
 * @return void
 */
function highlight_route($route)
{
    $rota = get_route();
    if ($rota['route'] == $route) {
        echo 'active';
    }
}

// Função para conectar ao banco de dados usando .env
// Função para conectar ao banco de dados usando .env
function getConnection() {
    // Usa operador de coalescência nula para definir valores padrão
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_NAME'] ?? 'paws_patterns';
    $username = $_ENV['DB_USER'] ?? 'root';
    $password = $_ENV['DB_PASS'] ?? 'root';
    
    $dsn = sprintf('mysql:host=%s;dbname=%s', $host, $dbname);
    
    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        die(json_encode(['error' => 'Erro na conexão: ' . $e->getMessage()]));
    }
}