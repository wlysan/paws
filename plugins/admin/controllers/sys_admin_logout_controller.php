<?php
/**
 * Admin Logout Controller
 * 
 * Handles admin logout functionality
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if (isset($_SESSION['admin_id'])) {
    try {
        // Get database connection
        $pdo = getConnection();
        
        // If remember me cookie is set, invalidate the token
        if (isset($_COOKIE['admin_remember'])) {
            $token = $_COOKIE['admin_remember'];
            
            // Mark token as used in the database
            $stmt = $pdo->prepare("
                UPDATE admin_auth_tokens 
                SET is_used = 1 
                WHERE token = ?
            ");
            
            $stmt->execute([$token]);
            
            // Remove the cookie
            setcookie('admin_remember', '', time() - 3600, '/', '', true, true);
        }
        
        // Log the logout activity
        $stmt = $pdo->prepare("
            INSERT INTO admin_activity_logs (
                admin_id, 
                activity_type, 
                details, 
                ip_address, 
                user_agent, 
                activity_time
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_SESSION['admin_id'],
            'logout',
            'Admin user logged out',
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'],
            date('Y-m-d H:i:s')
        ]);
        
    } catch (PDOException $e) {
        // Log error but continue with logout
        error_log('Admin logout error: ' . $e->getMessage());
    }
    
    // Clear all session variables
    $_SESSION = [];
    
    // If session cookie is used, unset it
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}

// Redirect to login page
header('Location: /index.php/admin/login');
exit;