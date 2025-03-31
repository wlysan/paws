<?php
// Incluir os componentes de alertas
include_once 'plugins/admin/components/alerts.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Paws & Patterns</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="/assets/bootstrap-5.2.3-dist/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/plugins/admin/css/sys_login.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Old+Standard+TT:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-wrapper">
            <div class="row g-0">
                <!-- Left Column - Image -->
                <div class="col-md-6 d-none d-md-block">
                    <div class="login-image">
                        <div class="overlay"></div>
                    </div>
                </div>
                
                <!-- Right Column - Login Form -->
                <div class="col-md-6">
                    <div class="login-form-wrapper">
                        <div class="login-header">
                            <div class="logo-container">
                                <div class="logo-image-container">
                                    <img src="/assets/img/logo.svg" alt="Paws & Patterns" class="logo-image">
                                </div>
                                <h1 class="logo-text">Paws & Patterns</h1>
                            </div>
                            <h2 class="login-title">Administrator Login</h2>
                            <p class="login-subtitle">Enter your credentials to access the dashboard</p>
                        </div>
                        
                        <!-- Display alerts from session -->
                        <?php show_session_alerts(); ?>
                        
                        <!-- Display specific error message if set -->
                        <?php if(isset($error_message) && !empty($error_message)): ?>
                            <?php show_error_alert($error_message); ?>
                        <?php endif; ?>
                        
                        <!-- Display registration success message if available -->
                        <?php if(isset($_SESSION['registro_sucesso'])): ?>
                            <?php show_success_alert($_SESSION['registro_sucesso']); unset($_SESSION['registro_sucesso']); ?>
                        <?php endif; ?>
                        
                        <form action="/index.php/admin/login" method="post" class="login-form">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" required placeholder="Enter your email">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required placeholder="Enter your password">
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                                <a href="/index.php/admin/forgot-password" class="float-end forgot-password">Forgot password?</a>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                        </form>
                        
                        <div class="login-footer">
                            <p class="text-center">Don't have an account? <a href="/index.php/admin/register">Register here</a></p>
                            <p class="text-center mt-3">&copy; <?php echo date('Y'); ?> Paws & Patterns. All Rights Reserved.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="/assets/bootstrap-5.2.3-dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="/plugins/admin/js/sys_login.js"></script>
</body>
</html>