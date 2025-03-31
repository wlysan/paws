<?php
/**
 * Admin Registration Controller
 * 
 * Handles registration for admin users
 */

// Inicie a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize messages
$error_message = '';
$success_message = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $admin_role = isset($_POST['admin_role']) ? $_POST['admin_role'] : '';
    $terms = isset($_POST['terms']) ? true : false;
    
    // Validate form data
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password) || empty($admin_role)) {
        $error_message = 'All required fields must be filled out.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error_message = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } elseif (!in_array($admin_role, ['admin', 'super_admin', 'editor'])) {
        $error_message = 'Invalid admin role selected.';
    } elseif (!$terms) {
        $error_message = 'You must agree to the Terms & Conditions.';
    } else {
        try {
            $pdo = getConnection();
            
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM admin_users WHERE email = ?");
            $stmt->execute([$email]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                $error_message = 'Email address is already registered.';
            } else {
                // Generate verification token
                $verification_token = bin2hex(random_bytes(32));
                
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new admin user
                $stmt = $pdo->prepare("
                    INSERT INTO admin_users (
                        first_name, 
                        last_name, 
                        email, 
                        phone,
                        password, 
                        role, 
                        status,
                        verification_token,
                        created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $first_name,
                    $last_name,
                    $email,
                    $phone,
                    $hashed_password,
                    $admin_role,
                    'pending', // Initial status is pending until verified
                    $verification_token,
                    date('Y-m-d H:i:s')
                ]);
                
                // Get the new admin_id
                $admin_id = $pdo->lastInsertId();
                
                // Create notification for admin approval
                $stmt = $pdo->prepare("
                    INSERT INTO admin_notifications (
                        type,
                        message,
                        link,
                        is_read,
                        created_at
                    ) VALUES (?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    'new_admin',
                    "New admin registration: {$first_name} {$last_name}",
                    "/index.php/admin/users/verify/{$admin_id}",
                    0,
                    date('Y-m-d H:i:s')
                ]);
                
                // Log activity
                $ip = $_SERVER['REMOTE_ADDR'];
                $userAgent = $_SERVER['HTTP_USER_AGENT'];
                
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
                    $admin_id,
                    'registration',
                    'Admin user registered',
                    $ip,
                    $userAgent,
                    date('Y-m-d H:i:s')
                ]);
                
                // Set success message in session and redirect to login page
                $_SESSION['success_message'] = 'Registration successful! Please check your email to verify your account and wait for admin approval.';
                header('Location: /index.php/admin/login');
                exit;
            }
        } catch (PDOException $e) {
            $error_message = 'Database error. Please try again later.';
            // Log the error
            error_log('Admin registration error: ' . $e->getMessage());
        }
    }
}