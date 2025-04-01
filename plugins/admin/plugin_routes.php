<?php
/**
 * Plugin Routes for Admin
 * 
 * Defines routes specific to the admin plugin
 */

$plugin_route['admin'] = [
    '/admin/login' => [
        'view' => 'plugins/admin/views/sys_admin_login_view.php',
        'controller' => 'plugins/admin/controllers/sys_admin_login_controller.php',
        'structure' => 'app/struct/blank.php'  // Using blank structure for login page
    ],
    '/admin/register' => [
        'view' => 'plugins/admin/views/sys_admin_register_view.php',
        'controller' => 'plugins/admin/controllers/sys_admin_register_controller.php',
        'structure' => 'app/struct/blank.php'  // Using blank structure for register page
    ],
    '/admin/dashboard' => [
        'view' => 'plugins/admin/views/sys_admin_dashboard_view.php',
        'controller' => 'plugins/admin/controllers/sys_admin_dashboard_controller.php',
        'structure' => 'plugins/admin/struct/sys_admin_struct.php'
    ]
];