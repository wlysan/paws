<?php
/**
 * Admin Login Controller
 * 
 * Handles authentication for admin users
 */

// Inicie a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize error message
$error_message = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validate form data
    if (empty($email) || empty($password)) {
        $error_message = 'Email and password are required.';
    } else {
        // Connect to database
        try {
            $pdo = getConnection();
            
            // Prepare and execute query
            $stmt = $pdo->prepare("
                SELECT * FROM admin_users 
                WHERE email = ? AND status = 'active' AND is_deleted = 0
            ");
            $stmt->execute([$email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if user was found but is pending
            $stmt_pending = $pdo->prepare("
                SELECT * FROM admin_users 
                WHERE email = ? AND status = 'pending' AND is_deleted = 0
            ");
            $stmt_pending->execute([$email]);
            $pending_admin = $stmt_pending->fetch(PDO::FETCH_ASSOC);
            
            // If user is pending, show appropriate message
            if ($pending_admin) {
                $error_message = 'Your account is pending approval. Please wait for administrator verification.';
                $_SESSION['info_message'] = 'If you need immediate assistance, please contact an administrator.';
            }
            // If user is locked, show appropriate message
            else if ($admin && $admin['status'] == 'locked') {
                $error_message = 'Your account is temporarily locked. Please try again later or contact an administrator.';
            }
            // Verify user and password
            else if ($admin && password_verify($password, $admin['password'])) {
                // Set session data
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['admin_last_login'] = date('Y-m-d H:i:s');
                
                // If remember me is checked, set cookie
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expiry = time() + (30 * 24 * 60 * 60); // 30 days
                    
                    // Store token in database
                    $stmt = $pdo->prepare("
                        INSERT INTO admin_auth_tokens (admin_id, token, expiry_date, is_used) 
                        VALUES (?, ?, ?, 0)
                    ");
                    $stmt->execute([$admin['id'], $token, date('Y-m-d H:i:s', $expiry)]);
                    
                    // Set cookie
                    setcookie('admin_remember', $token, $expiry, '/', '', true, true);
                }
                
                // Update last login time
                $stmt = $pdo->prepare("
                    UPDATE admin_users 
                    SET last_login = ? 
                    WHERE id = ?
                ");
                $stmt->execute([date('Y-m-d H:i:s'), $admin['id']]);
                
                // Log login activity
                $ip = $_SERVER['REMOTE_ADDR'];
                $userAgent = $_SERVER['HTTP_USER_AGENT'];
                
                $stmt = $pdo->prepare("
                    INSERT INTO admin_login_logs (admin_id, ip_address, user_agent, login_time) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$admin['id'], $ip, $userAgent, date('Y-m-d H:i:s')]);
                
                // Set success message
                $_SESSION['success_message'] = 'Login successful! Welcome back, ' . $admin['first_name'] . '.';
                
                // Redirect to admin dashboard
                header('Location: /index.php/admin/dashboard');
                exit;
            } else {
                $error_message = 'Invalid email or password.';
                
                // Log failed login attempt if user exists
                if ($admin) {
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $userAgent = $_SERVER['HTTP_USER_AGENT'];
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO admin_failed_logins (email, ip_address, user_agent, attempt_time) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$email, $ip, $userAgent, date('Y-m-d H:i:s')]);
                    
                    // Check for multiple failed attempts
                    $stmt = $pdo->prepare("
                        SELECT COUNT(*) as count 
                        FROM admin_failed_logins 
                        WHERE email = ? AND attempt_time > ?
                    ");
                    $stmt->execute([$email, date('Y-m-d H:i:s', strtotime('-30 minutes'))]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // If too many failed attempts, lock the account temporarily
                    if ($result['count'] >= 5) {
                        $stmt = $pdo->prepare("
                            UPDATE admin_users 
                            SET status = 'locked', 
                                locked_until = ? 
                            WHERE email = ?
                        ");
                        $stmt->execute([date('Y-m-d H:i:s', strtotime('+30 minutes')), $email]);
                        
                        $error_message = 'Account temporarily locked due to multiple failed login attempts. Please try again later.';
                    }
                }
            }
        } catch (PDOException $e) {
            $error_message = 'Database error. Please try again later.';
            // Log the error
            error_log('Admin login error: ' . $e->getMessage());
        }
    }
}

// Check for an existing "remember me" cookie
if (!isset($_SESSION['admin_id']) && isset($_COOKIE['admin_remember'])) {
    $token = $_COOKIE['admin_remember'];
    
    try {
        $pdo = getConnection();
        
        // Get token from database
        $stmt = $pdo->prepare("
            SELECT at.*, au.* 
            FROM admin_auth_tokens at 
            JOIN admin_users au ON at.admin_id = au.id 
            WHERE at.token = ? 
            AND at.is_used = 0 
            AND at.expiry_date > ?
            AND au.status = 'active'
            AND au.is_deleted = 0
        ");
        $stmt->execute([$token, date('Y-m-d H:i:s')]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // Set session data
            $_SESSION['admin_id'] = $result['admin_id'];
            $_SESSION['admin_name'] = $result['first_name'] . ' ' . $result['last_name'];
            $_SESSION['admin_email'] = $result['email'];
            $_SESSION['admin_role'] = $result['role'];
            $_SESSION['admin_last_login'] = date('Y-m-d H:i:s');
            
            // Mark token as used
            $stmt = $pdo->prepare("
                UPDATE admin_auth_tokens 
                SET is_used = 1 
                WHERE token = ?
            ");
            $stmt->execute([$token]);
            
            // Create a new token
            $newToken = bin2hex(random_bytes(32));
            $expiry = time() + (30 * 24 * 60 * 60); // 30 days
            
            // Store new token in database
            $stmt = $pdo->prepare("
                INSERT INTO admin_auth_tokens (admin_id, token, expiry_date, is_used) 
                VALUES (?, ?, ?, 0)
            ");
            $stmt->execute([$result['admin_id'], $newToken, date('Y-m-d H:i:s', $expiry)]);
            
            // Set new cookie
            setcookie('admin_remember', $newToken, $expiry, '/', '', true, true);
            
            // Set message for automatic login
            $_SESSION['info_message'] = 'You have been automatically logged in using your remembered session.';
            
            // Redirect to admin dashboard
            header('Location: /index.php/admin/dashboard');
            exit;
        } else {
            // Invalid or expired token
            setcookie('admin_remember', '', time() - 3600, '/', '', true, true);
        }
    } catch (PDOException $e) {
        error_log('Admin remember token error: ' . $e->getMessage());
    }
}