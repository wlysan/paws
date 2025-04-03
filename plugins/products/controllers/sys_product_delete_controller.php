<?php
/**
 * Admin Product Controller - Delete
 * 
 * Handles deletion of products with their images and relationships
 */

// Require admin login if the function exists
if (function_exists('require_admin_login')) {
    require_admin_login();
}

// Process deletion only via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.php/admin/products');
    exit;
}

// Get product ID
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

if ($product_id <= 0) {
    $_SESSION['error_message'] = 'Invalid product ID';
    header('Location: /index.php/admin/products');
    exit;
}

try {
    $pdo = getConnection();
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Check if the product exists
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        $_SESSION['error_message'] = 'Product not found';
        header('Location: /index.php/admin/products');
        exit;
    }
    
    // Get product images to delete files
    $stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Delete image files from the server
    foreach ($images as $image) {
        if (!empty($image['image_path']) && file_exists($image['image_path'])) {
            unlink($image['image_path']);
            error_log('Deleted product image file: ' . $image['image_path']);
        }
    }
    
    // Delete product images from database
    $stmt = $pdo->prepare("DELETE FROM product_images WHERE product_id = ?");
    $stmt->execute([$product_id]);
    
    // Remove category relationships
    $stmt = $pdo->prepare("DELETE FROM product_category_relationships WHERE product_id = ?");
    $stmt->execute([$product_id]);
    
    // Perform soft delete on the product
    $stmt = $pdo->prepare("
        UPDATE products 
        SET is_deleted = 1, 
            deleted_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$product_id]);
    
    // Commit transaction
    $pdo->commit();
    
    // Register activity if the function exists
    if (function_exists('log_admin_activity')) {
        log_admin_activity('product_deleted', 'Product deleted: ' . $product['name']);
    }
    
    $_SESSION['success_message'] = 'Product deleted successfully';
    
} catch (PDOException $e) {
    // Rollback transaction on exception
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $_SESSION['error_message'] = 'Error in database while deleting product';
    error_log('Error deleting product: ' . $e->getMessage());
}

// Redirect back to the products list
header('Location: /index.php/admin/products');
exit;