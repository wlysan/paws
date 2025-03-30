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
    $retorno = ['route' => '/home', 'controller' => null, 'action' => null];
    
    // Check if the URL contains /index.php
    if (strpos($url, '/index.php') !== false) {
        // Extract the path after /index.php
        $parts = explode('/index.php', $url, 2);
        
        if (isset($parts[1]) && !empty($parts[1])) {
            $path_parts = explode('/', trim($parts[1], '/'));
            
            // The first part after /index.php is the route
            if (!empty($path_parts[0])) {
                $retorno['route'] = '/' . $path_parts[0];
                
                // The second part is either the controller or the action
                if (isset($path_parts[1]) && !empty($path_parts[1])) {
                    if (isset($path_parts[2]) && !empty($path_parts[2])) {
                        // If there's a third part, then the second is controller and third is action
                        $retorno['controller'] = $path_parts[1];
                        $retorno['action'] = $path_parts[2];
                    } else {
                        // If there's no third part, the second is the action
                        $retorno['action'] = $path_parts[1];
                    }
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
function get_view($view)
{
    global $routes;

    if (array_key_exists($view, $routes)) {
        $view_add = $routes[$view]['view'];
        return $view_add;
    }
}

function get_std_controller($view)
{
    global $routes;
    global $view_act;
    global $action;
    global $pdo;

    $view_act = $view;

    if (array_key_exists($view, $routes)) {
        $controller_add = $routes[$view]['controller'];
        if (isset($controller_add) && $controller_add != '') {
            if (file_exists($controller_add)) {
                include $controller_add;
            }
        }
    }
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

function get_action()
{
    $rota = get_route();

    if (isset($rota['action']) && $rota['action'] != '') {
        $action = $rota['action'];
        return $action;
    }

    return null;
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
function get_structure($view)
{
    global $routes;

    if (array_key_exists($view, $routes)) {
        //$structure = $routes[$view]['structure'];
        $structure = isset($routes[$view]['structure']) ? $routes[$view]['structure'] : '';

        if ($structure == '') {
            $structure = $GLOBALS['core_structure'];
        }
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
    // Check if the directory exists
    // Get the list of files and directories
    $filesAndFolders = scandir($directory);

    foreach ($filesAndFolders as $item) {
        // Construct the full path
        $fullPath = $directory . '/' . $item;

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
function getConnection() {
    $dsn = sprintf('mysql:host=%s;dbname=%s', $_ENV['DB_HOST'], $_ENV['DB_NAME']);
    $username = $_ENV['DB_USER'];
    $password = $_ENV['DB_PASS'];
    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        die(json_encode(['error' => 'Erro na conexão: ' . $e->getMessage()]));
    }
}