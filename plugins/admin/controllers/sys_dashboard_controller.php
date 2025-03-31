<?php
/**
 * Admin Dashboard Controller
 * 
 * Handles data preparation for the admin dashboard
 */

// Require admin login
require_admin_login();

// Check if we need to display any messages from redirects
$success_message = '';
$error_message = '';

// Process any request parameters
$action = isset($_GET['action']) ? $_GET['action'] : '';

// If an action is specified, process it
if (!empty($action)) {
    switch ($action) {
        case 'refresh_stats':
            // This would be where we'd refresh statistics if needed
            $_SESSION['admin_success'] = 'Dashboard statistics refreshed successfully.';
            
            // Redirect to remove the action parameter
            header('Location: /index.php/admin/dashboard');
            exit;
            
            
        // Add more actions as needed
    }
}

// Fetch real statistics (placeholder for now)
// In a real implementation, these would be fetched from database
$stats = [
    'orders' => [
        'total' => 156,
        'new' => 24,
        'processing' => 12,
        'completed' => 134,
        'cancelled' => 10
    ],
    'revenue' => [
        'total' => 8745.50,
        'today' => 856.20,
        'this_week' => 2156.80,
        'this_month' => 8745.50
    ],
    'customers' => [
        'total' => 86,
        'new_today' => 3,
        'new_this_week' => 12,
        'new_this_month' => 32
    ],
    'products' => [
        'total' => 178,
        'active' => 154,
        'out_of_stock' => 15,
        'low_stock' => 22
    ]
];

// Fetch recent orders (placeholder for now)
$recent_orders = [
    [
        'id' => 'ORD-0025',
        'customer' => 'John O\'Sullivan',
        'product' => 'Premium Dog Collar',
        'date' => '2025-03-30',
        'amount' => 45.99,
        'status' => 'completed'
    ],
    [
        'id' => 'ORD-0024',
        'customer' => 'Mary Ryan',
        'product' => 'Cat Bow Tie Set',
        'date' => '2025-03-29',
        'amount' => 32.50,
        'status' => 'processing'
    ],
    [
        'id' => 'ORD-0023',
        'customer' => 'Sean Murphy',
        'product' => 'Dog Raincoat',
        'date' => '2025-03-28',
        'amount' => 59.99,
        'status' => 'shipped'
    ],
    [
        'id' => 'ORD-0022',
        'customer' => 'Aoife Kelly',
        'product' => 'Pet Bow Tie Collection',
        'date' => '2025-03-28',
        'amount' => 85.00,
        'status' => 'cancelled'
    ],
    [
        'id' => 'ORD-0021',
        'customer' => 'Liam O\'Connor',
        'product' => 'Luxury Pet Bed',
        'date' => '2025-03-27',
        'amount' => 129.99,
        'status' => 'completed'
    ]
];

// Fetch top selling products (placeholder for now)
$top_products = [
    [
        'name' => 'Premium Dog Collar',
        'category' => 'Pet Accessories',
        'sold' => 124
    ],
    [
        'name' => 'Luxury Pet Bed',
        'category' => 'Pet Furniture',
        'sold' => 98
    ],
    [
        'name' => 'Cat Bow Tie Set',
        'category' => 'Cat Accessories',
        'sold' => 85
    ],
    [
        'name' => 'Dog Raincoat',
        'category' => 'Dog Clothing',
        'sold' => 72
    ],
    [
        'name' => 'Pet Bow Tie Collection',
        'category' => 'Special Edition',
        'sold' => 63
    ]
];

// Fetch recent customers (placeholder for now)
$recent_customers = [
    [
        'name' => 'Aoife Kelly',
        'email' => 'aoife.kelly@example.com',
        'joined' => '2025-03-28'
    ],
    [
        'name' => 'Liam O\'Connor',
        'email' => 'liam.oconnor@example.com',
        'joined' => '2025-03-27'
    ],
    [
        'name' => 'Niamh Byrne',
        'email' => 'niamh.byrne@example.com',
        'joined' => '2025-03-26'
    ],
    [
        'name' => 'Conor Daly',
        'email' => 'conor.daly@example.com',
        'joined' => '2025-03-25'
    ]
];

// Fetch recent activities (placeholder for now)
$recent_activities = [
    [
        'timestamp' => '2025-03-30 14:25:00',
        'type' => 'order_status_changed',
        'title' => 'Order #ORD-0025 was completed',
        'details' => 'Status changed from Processing to Completed'
    ],
    [
        'timestamp' => '2025-03-30 11:42:00',
        'type' => 'product_added',
        'title' => 'New product was added',
        'details' => 'Luxury Cat Harness (SKU: LCH-001)'
    ],
    [
        'timestamp' => '2025-03-29 16:18:00',
        'type' => 'order_received',
        'title' => 'Order #ORD-0024 was received',
        'details' => 'From: Mary Ryan'
    ],
    [
        'timestamp' => '2025-03-29 10:05:00',
        'type' => 'inventory_update',
        'title' => 'Product stock update',
        'details' => 'Dog Raincoat inventory: +15 units'
    ],
    [
        'timestamp' => '2025-03-28 15:30:00',
        'type' => 'customer_registration',
        'title' => 'New customer registered',
        'details' => 'Aoife Kelly (aoife.kelly@example.com)'
    ]
];