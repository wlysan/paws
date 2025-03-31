<?php
/**
 * Admin Plugin Autoload
 * 
 * This file is included automatically when the plugin is loaded
 */

// Include plugin routes
include_once "plugin_routes.php";

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Session Security Helper Functions
 */

/**
 * Check if admin is logged in
 * 
 * @return bool True if admin is logged in, false otherwise
 */
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Check admin permissions
 * 
 * @param string $permission The permission to check
 * @return bool True if admin has permission, false otherwise
 */
function admin_has_permission($permission) {
    if (!is_admin_logged_in()) {
        return false;
    }
    
    try {
        $pdo = getConnection();
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM admin_role_permissions rp
            JOIN admin_permissions p ON rp.permission_id = p.id
            JOIN admin_users u ON rp.role = u.role
            WHERE u.id = ? AND p.name = ?
        ");
        
        $stmt->execute([$_SESSION['admin_id'], $permission]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return ($result['count'] > 0);
    } catch (PDOException $e) {
        error_log('Admin permission check error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get admin data
 * 
 * @param int $admin_id The admin ID (defaults to current logged in admin)
 * @return array|null Admin data or null if not found
 */
function get_admin_data($admin_id = null) {
    if ($admin_id === null) {
        if (!is_admin_logged_in()) {
            return null;
        }
        $admin_id = $_SESSION['admin_id'];
    }
    
    try {
        $pdo = getConnection();
        
        $stmt = $pdo->prepare("
            SELECT id, first_name, last_name, email, phone, role, status, profile_image, last_login, created_at 
            FROM admin_users 
            WHERE id = ? AND is_deleted = 0
        ");
        
        $stmt->execute([$admin_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Get admin data error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Require admin login for protected routes
 * 
 * Redirects to login page if not logged in
 */
function require_admin_login() {
    if (!is_admin_logged_in()) {
        header('Location: /index.php/admin/login');
        exit;
    }
}

/**
 * Require specific admin permission
 * 
 * Redirects to dashboard with error if permission not granted
 * 
 * @param string $permission The required permission
 */
function require_admin_permission($permission) {
    require_admin_login();
    
    if (!admin_has_permission($permission)) {
        $_SESSION['admin_error'] = 'You do not have permission to access this page.';
        header('Location: /index.php/admin/dashboard');
        exit;
    }
}

/**
 * Get unread notifications count for current admin
 * 
 * @return int Number of unread notifications
 */
function get_admin_unread_notifications_count() {
    if (!is_admin_logged_in()) {
        return 0;
    }
    
    try {
        $pdo = getConnection();
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM admin_notifications 
            WHERE (admin_id = ? OR admin_id IS NULL) 
            AND is_read = 0
        ");
        
        $stmt->execute([$_SESSION['admin_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'];
    } catch (PDOException $e) {
        error_log('Get admin notifications count error: ' . $e->getMessage());
        return 0;
    }
}

// Hook for adding items to main navigation
add_hook('menu_lateral_items', function() {
    if (is_admin_logged_in()) {
        echo '<li>
                <a href="/index.php/admin/dashboard" class="item">
                    <div class="icon-box bg-primary">
                        <ion-icon name="speedometer-outline"></ion-icon>
                    </div>
                    <div class="in">
                        Admin Dashboard
                    </div>
                </a>
            </li>';
    }
});