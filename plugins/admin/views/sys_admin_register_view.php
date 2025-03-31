<?php
// Incluir os componentes de alertas
include_once 'plugins/admin/components/alerts.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration - Paws & Patterns</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="/assets/bootstrap-5.2.3-dist/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/plugins/admin/css/sys_register.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Old+Standard+TT:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="register-container">
        <div class="register-wrapper">
            <div class="row g-0">
                <!-- Left Column - Image -->
                <div class="col-md-6 d-none d-md-block">
                    <div class="register-image">
                        <div class="overlay"></div>
                    </div>
                </div>
                
                <!-- Right Column - Registration Form -->
                <div class="col-md-6">
                    <div class="register-form-wrapper">
                        <div class="register-header">
                            <div class="logo-container">
                                <div class="logo-image-container">
                                    <img src="/assets/img/logo.svg" alt="Paws & Patterns" class="logo-image">
                                </div>
                                <h1 class="logo-text">Paws & Patterns</h1>
                            </div>
                            <h2 class="register-title">Create Admin Account</h2>
                            <p class="register-subtitle">Fill in the form below to register for dashboard access</p>
                        </div>
                        
                        <!-- Display alerts from session -->
                        <?php show_session_alerts(); ?>
                        
                        <!-- Display specific error message if set -->
                        <?php if(isset($error_message) && !empty($error_message)): ?>
                            <?php show_error_alert($error_message); ?>
                        <?php endif; ?>
                        
                        <!-- Display success message if set -->
                        <?php if(isset($success_message) && !empty($success_message)): ?>
                            <?php show_success_alert($success_message); ?>
                        <?php endif; ?>
                        
                        <form action="/index.php/admin/register" method="post" class="register-form">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="first_name" name="first_name" required placeholder="Enter first name">
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="last_name" name="last_name" required placeholder="Enter last name">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" required placeholder="Enter your email">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter phone number">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required placeholder="Enter password" minlength="8">
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="form-text">Password must be at least 8 characters long</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Confirm password">
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="admin_role" class="form-label">Admin Role</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-shield"></i></span>
                                    <select class="form-select" id="admin_role" name="admin_role" required>
                                        <option value="" selected disabled>Select a role</option>
                                        <option value="admin">Admin</option>
                                        <option value="super_admin">Super Admin</option>
                                        <option value="editor">Editor</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">I agree to the <a href="/terms" target="_blank">Terms & Conditions</a></label>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Register</button>
                            </div>
                        </form>
                        
                        <div class="register-footer">
                            <p class="text-center">Already have an account? <a href="/index.php/admin/login">Login here</a></p>
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
    <script src="/plugins/admin/js/sys_register.js"></script>
</body>
</html>