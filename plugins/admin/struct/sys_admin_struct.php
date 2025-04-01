<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paws & Patterns - Admin</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="/assets/bootstrap-5.2.3-dist/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/plugins/admin/css/sys_struct.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Old+Standard+TT:wght@400;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <div class="logo-image-container">
                        <img src="/assets/img/logo.svg" alt="Paws & Patterns" class="logo-image">
                    </div>
                    <h2 class="logo-text">Paws & Patterns</h2>
                </div>
                <button class="sidebar-toggle d-md-none">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="sidebar-body">
                <ul class="sidebar-menu">
                    <li class="sidebar-item">
                        <a href="/index.php/admin/dashboard" class="sidebar-link">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="/index.php/admin/products" class="sidebar-link">
                            <i class="fas fa-box"></i>
                            <span>Products</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="/index.php/admin/categories" class="sidebar-link">
                            <i class="fas fa-tags"></i>
                            <span>Categories</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="/index.php/admin/orders" class="sidebar-link">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Orders</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="/index.php/admin/customers" class="sidebar-link">
                            <i class="fas fa-users"></i>
                            <span>Customers</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="/index.php/admin/pets" class="sidebar-link">
                            <i class="fas fa-paw"></i>
                            <span>Pets</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="/index.php/admin/reports" class="sidebar-link">
                            <i class="fas fa-chart-bar"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="/index.php/admin/settings" class="sidebar-link">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="sidebar-footer">
                <a href="/index.php/admin/logout" class="logout-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- Main content -->
        <div class="main-content">
            <!-- Top Navigation -->
            <nav class="top-nav">
                <div class="nav-left">
                    <button class="sidebar-toggle d-none d-md-block">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                <div class="nav-right">
                    <div class="dropdown">
                        <button class="notification-btn" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                            <li>
                                <h6 class="dropdown-header">Notifications</h6>
                            </li>
                            <li><a class="dropdown-item" href="#">New order received</a></li>
                            <li><a class="dropdown-item" href="#">Product stock low</a></li>
                            <li><a class="dropdown-item" href="#">New customer registration</a></li>
                        </ul>
                    </div>
                    <div class="dropdown">
                        <button class="user-dropdown-btn" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="/assets/img/avatar.jpg" alt="User" class="user-avatar">
                            <span class="user-name">Admin User</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="/index.php/admin/profile">Profile</a></li>
                            <li><a class="dropdown-item" href="/index.php/admin/settings">Settings</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="/index.php/admin/logout">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <div class="page-content">
                <?php
                // Executar o controller e obter suas variáveis
                $controller_vars = get_std_controller($rota['route']);

                // Incluir a view
                $view_path = get_view($rota['route']);
                if (file_exists($view_path)) {
                    include $view_path;
                } else {
                    echo "<div class='alert alert-danger'>View não encontrada: " . htmlspecialchars($rota['route']) . "</div>";
                }
                ?>
            </div>

            <!-- Footer -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            <p>&copy; <?php echo date('Y'); ?> Paws & Patterns. All Rights Reserved.</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p>Admin Dashboard v1.0</p>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="/assets/bootstrap-5.2.3-dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="/plugins/admin/js/sys_struct.js"></script>
</body>

</html>