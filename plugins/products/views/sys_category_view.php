<?php

print_r(get_route());
print_r(get_parameters());



// Include any components needed
if (file_exists('plugins/admin/components/alerts.php')) {
    include_once 'plugins/admin/components/alerts.php';
}
if (file_exists('plugins/admin/components/modals.php')) {
    include_once 'plugins/admin/components/modals.php';
}

// Enforce admin login if function exists
if (function_exists('require_admin_login')) {
    require_admin_login();
}
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Product Categories</h1>
            <p class="text-muted">Manage product categories for your store</p>
        </div>
        <div>
            <a href="/index.php/admin/categories/add" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i> Add New Category
            </a>
        </div>
    </div>
    <!-- Display alerts if available -->
    <?php if (function_exists('show_session_alerts')) show_session_alerts(); ?>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <!-- Categories Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Categories</h5>
        </div>
        <div class="card-body">
            <?php if (empty($categories)): ?>
                <div class="alert alert-info">
                    No categories found. Create your first category to get started.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Parent</th>
                                <th>Status</th>
                                <th>Products</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($category['image_path']) && file_exists($category['image_path'])): ?>
                                            <img src="/<?php echo htmlspecialchars($category['image_path']); ?>"
                                                alt="<?php echo htmlspecialchars($category['name']); ?>"
                                                class="category-thumb" width="50" height="50">
                                        <?php else: ?>
                                            <div class="no-image">No image</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        // Add indentation for subcategories
                                        if (!empty($category['parent_id'])) {
                                            echo '<span class="subcategory-indent">└─ </span>';
                                        }
                                        echo htmlspecialchars($category['name']);
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(substr($category['description'], 0, 100)) . (strlen($category['description']) > 100 ? '...' : ''); ?></td>
                                    <td><?php echo !empty($category['parent_name']) ? htmlspecialchars($category['parent_name']) : '<i>None</i>'; ?></td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        switch ($category['status']) {
                                            case 'active':
                                                $status_class = 'bg-success';
                                                break;
                                            case 'inactive':
                                                $status_class = 'bg-secondary';
                                                break;
                                            case 'featured':
                                                $status_class = 'bg-primary';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo ucfirst($category['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo (int)$category['product_count']; ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="/index.php/admin/categories/edit/id/<?php echo $category['id']; ?>" 
                                                class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteModal<?php echo $category['id']; ?>">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </button>
                                        </div>

                                        <!-- Delete Modal -->
                                        <?php
                                        $modal_message = '<p>Are you sure you want to delete the category <strong>' .
                                            htmlspecialchars($category['name']) . '</strong>?</p>';

                                        if ((int)$category['product_count'] > 0) {
                                            $modal_message .= '<div class="alert alert-warning">
                                                This category has ' . (int)$category['product_count'] . ' products associated with it. 
                                                Deleting this category will remove the association but not delete the products.
                                            </div>';
                                        }

                                        // Check for subcategories
                                        $has_subcategories = false;
                                        foreach ($categories as $sub) {
                                            if ($sub['parent_id'] == $category['id']) {
                                                $has_subcategories = true;
                                                break;
                                            }
                                        }

                                        if ($has_subcategories) {
                                            $modal_message .= '<div class="alert alert-danger">
                                                This category has subcategories. You must delete or reassign the subcategories first.
                                            </div>';
                                            $confirm_action = '';
                                        } else {
                                            $confirm_action = '
                                                document.getElementById("delete-form-' . $category['id'] . '").submit();
                                            ';
                                        }

                                        confirmation_modal(
                                            'deleteModal' . $category['id'],
                                            'Delete Category',
                                            $modal_message,
                                            'Delete',
                                            'Cancel',
                                            $confirm_action
                                        );
                                        ?>

                                        <!-- Hidden Delete Form -->
                                        <form id="delete-form-<?php echo $category['id']; ?>"
                                            action="/index.php/admin/categories" method="post" style="display:none;">
                                            <input type="hidden" name="action" value="delete_category">
                                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .category-thumb {
        object-fit: cover;
        border-radius: 4px;
    }

    .no-image {
        width: 50px;
        height: 50px;
        background-color: #f5f5f5;
        border: 1px dashed #ccc;
        border-radius: 4px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 10px;
        color: #999;
    }

    .subcategory-indent {
        color: #999;
        margin-right: 5px;
    }
</style>