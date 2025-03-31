<?php
// Require admin login
require_admin_login();

// Get admin data
$admin = get_admin_data();
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
        <p class="text-muted">Welcome back, <?php echo htmlspecialchars($admin['first_name']); ?>!</p>
    </div>
    
    <?php if(isset($_SESSION['admin_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['admin_success']; unset($_SESSION['admin_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['admin_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['admin_error']; unset($_SESSION['admin_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <!-- Stats Cards Row -->
    <div class="row">
        <!-- Orders Card -->
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="stats-card">
                <div class="stats-icon orders">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stats-details">
                    <h3>24</h3>
                    <p>New Orders</p>
                </div>
            </div>
        </div>
        
        <!-- Revenue Card -->
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="stats-card">
                <div class="stats-icon revenue">
                    <i class="fas fa-euro-sign"></i>
                </div>
                <div class="stats-details">
                    <h3>€2,156</h3>
                    <p>Total Revenue</p>
                </div>
            </div>
        </div>
        
        <!-- Customers Card -->
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="stats-card">
                <div class="stats-icon customers">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stats-details">
                    <h3>86</h3>
                    <p>Total Customers</p>
                </div>
            </div>
        </div>
        
        <!-- Products Card -->
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="stats-card">
                <div class="stats-icon products">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stats-details">
                    <h3>154</h3>
                    <p>Active Products</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <!-- Recent Orders -->
        <div class="col-lg-8 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Recent Orders</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Product</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#ORD-0025</td>
                                    <td>John O'Sullivan</td>
                                    <td>Premium Dog Collar</td>
                                    <td>30 Mar 2025</td>
                                    <td>€45.99</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                    <td><a href="#" class="btn btn-sm btn-primary">View</a></td>
                                </tr>
                                <tr>
                                    <td>#ORD-0024</td>
                                    <td>Mary Ryan</td>
                                    <td>Cat Bow Tie Set</td>
                                    <td>29 Mar 2025</td>
                                    <td>€32.50</td>
                                    <td><span class="badge bg-warning">Processing</span></td>
                                    <td><a href="#" class="btn btn-sm btn-primary">View</a></td>
                                </tr>
                                <tr>
                                    <td>#ORD-0023</td>
                                    <td>Sean Murphy</td>
                                    <td>Dog Raincoat</td>
                                    <td>28 Mar 2025</td>
                                    <td>€59.99</td>
                                    <td><span class="badge bg-info">Shipped</span></td>
                                    <td><a href="#" class="btn btn-sm btn-primary">View</a></td>
                                </tr>
                                <tr>
                                    <td>#ORD-0022</td>
                                    <td>Aoife Kelly</td>
                                    <td>Pet Bow Tie Collection</td>
                                    <td>28 Mar 2025</td>
                                    <td>€85.00</td>
                                    <td><span class="badge bg-danger">Cancelled</span></td>
                                    <td><a href="#" class="btn btn-sm btn-primary">View</a></td>
                                </tr>
                                <tr>
                                    <td>#ORD-0021</td>
                                    <td>Liam O'Connor</td>
                                    <td>Luxury Pet Bed</td>
                                    <td>27 Mar 2025</td>
                                    <td>€129.99</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                    <td><a href="#" class="btn btn-sm btn-primary">View</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="/index.php/admin/orders" class="btn btn-primary btn-sm">View All Orders</a>
                </div>
            </div>
        </div>
        
        <!-- Top Selling Products -->
        <div class="col-lg-4 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Top Selling Products</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Premium Dog Collar</h6>
                                <small class="text-muted">Pet Accessories</small>
                            </div>
                            <span class="badge bg-primary rounded-pill">124 sold</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Luxury Pet Bed</h6>
                                <small class="text-muted">Pet Furniture</small>
                            </div>
                            <span class="badge bg-primary rounded-pill">98 sold</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Cat Bow Tie Set</h6>
                                <small class="text-muted">Cat Accessories</small>
                            </div>
                            <span class="badge bg-primary rounded-pill">85 sold</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Dog Raincoat</h6>
                                <small class="text-muted">Dog Clothing</small>
                            </div>
                            <span class="badge bg-primary rounded-pill">72 sold</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Pet Bow Tie Collection</h6>
                                <small class="text-muted">Special Edition</small>
                            </div>
                            <span class="badge bg-primary rounded-pill">63 sold</span>
                        </li>
                    </ul>
                </div>
                <div class="card-footer text-end">
                    <a href="/index.php/admin/products" class="btn btn-primary btn-sm">Manage Products</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <!-- Recent Customers -->
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Recent Customers</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Email</th>
                                    <th>Joined</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Aoife Kelly</td>
                                    <td>aoife.kelly@example.com</td>
                                    <td>28 Mar 2025</td>
                                    <td><a href="#" class="btn btn-sm btn-primary">View</a></td>
                                </tr>
                                <tr>
                                    <td>Liam O'Connor</td>
                                    <td>liam.oconnor@example.com</td>
                                    <td>27 Mar 2025</td>
                                    <td><a href="#" class="btn btn-sm btn-primary">View</a></td>
                                </tr>
                                <tr>
                                    <td>Niamh Byrne</td>
                                    <td>niamh.byrne@example.com</td>
                                    <td>26 Mar 2025</td>
                                    <td><a href="#" class="btn btn-sm btn-primary">View</a></td>
                                </tr>
                                <tr>
                                    <td>Conor Daly</td>
                                    <td>conor.daly@example.com</td>
                                    <td>25 Mar 2025</td>
                                    <td><a href="#" class="btn btn-sm btn-primary">View</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="/index.php/admin/customers" class="btn btn-primary btn-sm">View All Customers</a>
                </div>
            </div>
        </div>
        
        <!-- Recent Activities -->
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Recent Activities</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-date">30 Mar, 14:25</div>
                            <div class="timeline-content">
                                <p class="mb-0"><strong>Order #ORD-0025</strong> was completed</p>
                                <small class="text-muted">Status changed from Processing to Completed</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-date">30 Mar, 11:42</div>
                            <div class="timeline-content">
                                <p class="mb-0"><strong>New product</strong> was added</p>
                                <small class="text-muted">Luxury Cat Harness (SKU: LCH-001)</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-date">29 Mar, 16:18</div>
                            <div class="timeline-content">
                                <p class="mb-0"><strong>Order #ORD-0024</strong> was received</p>
                                <small class="text-muted">From: Mary Ryan</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-date">29 Mar, 10:05</div>
                            <div class="timeline-content">
                                <p class="mb-0"><strong>Product stock update</strong></p>
                                <small class="text-muted">Dog Raincoat inventory: +15 units</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-date">28 Mar, 15:30</div>
                            <div class="timeline-content">
                                <p class="mb-0"><strong>New customer registered</strong></p>
                                <small class="text-muted">Aoife Kelly (aoife.kelly@example.com)</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="#" class="btn btn-primary btn-sm">View All Activities</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Additional CSS for timeline component */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-item:before {
    content: "";
    position: absolute;
    left: -30px;
    top: 0;
    width: 2px;
    height: 100%;
    background-color: #e9ecef;
}

.timeline-item:after {
    content: "";
    position: absolute;
    left: -36px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: var(--primary-color);
}

.timeline-date {
    font-size: 0.75rem;
    color: #6c757d;
    margin-bottom: 5px;
}

.timeline-content {
    background-color: #f8f9fa;
    padding: 10px 15px;
    border-radius: 5px;
}
</style>