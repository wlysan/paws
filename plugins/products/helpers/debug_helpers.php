<?php
/**
 * Debug helper function to be added to plugins/products/autoload.php
 */

/**
 * Debug function to log detailed information about requests and form data
 * 
 * @param string $message Message to log
 * @param mixed $data Data to log (optional)
 */
function debug_log($message, $data = null) {
    $log_message = '[Paws Debug] ' . $message;
    
    if ($data !== null) {
        if (is_array($data) || is_object($data)) {
            $log_message .= ' - Data: ' . print_r($data, true);
        } else {
            $log_message .= ' - Data: ' . $data;
        }
    }
    
    error_log($log_message);
}

/**
 * Debug helper to display debug information on screen
 */
function debug_display() {
    echo '<div style="background:#f8f9fa; border:1px solid #ddd; padding:15px; margin:15px; font-family:monospace">';
    echo '<h3>Debug Information</h3>';
    
    echo '<h4>Route Info</h4>';
    echo '<pre>';
    $route = get_route();
    print_r($route);
    echo '</pre>';
    
    echo '<h4>POST Data</h4>';
    echo '<pre>';
    print_r($_POST);
    echo '</pre>';
    
    echo '<h4>FILES Data</h4>';
    echo '<pre>';
    print_r($_FILES);
    echo '</pre>';
    
    echo '</div>';
}