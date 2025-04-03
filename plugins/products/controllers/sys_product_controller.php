<?php
/**
 * Admin Product Controller - Main
 * 
 * Handles the main listing of products in the admin interface
 */

// Require admin login if the function exists
if (function_exists('require_admin_login')) {
    require_admin_login();
}

// Inicializar variáveis globais para a view
global $success_message, $error_message;

// Load messages from session if they exist
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';

// Clear session messages after use
if (isset($_SESSION['success_message'])) {
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    unset($_SESSION['error_message']);
}

// Get active categories for the filter form
try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("
        SELECT id, name 
        FROM product_categories 
        WHERE status = 'active' AND is_deleted = 0
        ORDER BY name ASC
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error loading categories for filter: ' . $e->getMessage());
    $categories = [];
}

// Set categories in global scope for the view
$GLOBALS['categories'] = $categories;
$GLOBALS['success_message'] = $success_message;
$GLOBALS['error_message'] = $error_message;