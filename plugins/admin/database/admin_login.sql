-- Create admin_users table
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'super_admin', 'editor') NOT NULL DEFAULT 'admin',
    status ENUM('active', 'pending', 'suspended', 'locked') NOT NULL DEFAULT 'pending',
    profile_image VARCHAR(255),
    last_login DATETIME,
    locked_until DATETIME,
    verification_token VARCHAR(255),
    password_reset_token VARCHAR(255),
    password_reset_expires DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    is_deleted BOOLEAN DEFAULT FALSE,
    deleted_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admin_permissions table
CREATE TABLE admin_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admin_role_permissions junction table
CREATE TABLE admin_role_permissions (
    role ENUM('admin', 'super_admin', 'editor') NOT NULL,
    permission_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (role, permission_id),
    FOREIGN KEY (permission_id) REFERENCES admin_permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admin_auth_tokens table for "remember me" functionality
CREATE TABLE admin_auth_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expiry_date DATETIME NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admin_login_logs table
CREATE TABLE admin_login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    login_time DATETIME NOT NULL,
    FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admin_failed_logins table
CREATE TABLE admin_failed_logins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    attempt_time DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admin_activity_logs table
CREATE TABLE admin_activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    activity_time DATETIME NOT NULL,
    FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admin_notifications table
CREATE TABLE admin_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NULL, -- NULL for system-wide notifications
    type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default permissions
INSERT INTO admin_permissions (name, description) VALUES
('view_dashboard', 'Can view admin dashboard'),
('manage_products', 'Can manage products'),
('manage_categories', 'Can manage product categories'),
('manage_orders', 'Can manage customer orders'),
('manage_customers', 'Can manage customer accounts'),
('manage_admins', 'Can manage admin accounts'),
('manage_settings', 'Can manage system settings'),
('view_reports', 'Can view sales and performance reports'),
('manage_content', 'Can manage website content');

-- Insert default role permissions for super_admin
INSERT INTO admin_role_permissions (role, permission_id) 
SELECT 'super_admin', id FROM admin_permissions;

-- Insert default role permissions for admin
INSERT INTO admin_role_permissions (role, permission_id) 
SELECT 'admin', id FROM admin_permissions WHERE name != 'manage_admins' AND name != 'manage_settings';

-- Insert default role permissions for editor
INSERT INTO admin_role_permissions (role, permission_id) 
SELECT 'editor', id FROM admin_permissions WHERE name IN ('view_dashboard', 'manage_products', 'manage_categories', 'manage_content', 'view_reports');

-- Insert default super admin account
-- Password: Admin123!
INSERT INTO admin_users (
    first_name, 
    last_name, 
    email, 
    phone, 
    password, 
    role, 
    status, 
    created_at
) VALUES (
    'System', 
    'Administrator', 
    'admin@pawsandpatterns.com', 
    '+353 1 234 5678', 
    '$2y$10$q0OzlM/JXCvMSLDoDZIHFOh3S4/ZHDQAGerKFAzGAGY.WGXn0uFY2', 
    'super_admin', 
    'active', 
    NOW()
);