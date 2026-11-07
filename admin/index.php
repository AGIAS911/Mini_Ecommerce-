<?php
$pageTitle = 'Dashboard Insights';
require_once __DIR__ . '/includes/admin_header.php';
require_once __DIR__ . '/../classes/Order.php';
require_once __DIR__ . '/../classes/Product.php';
require_once __DIR__ . '/../classes/Category.php';
require_once __DIR__ . '/../classes/User.php';

$orderModel = new Order();
$productModel = new Product();
$categoryModel = new Category();
$userModel = new User();

$totalRevenue = $orderModel->getTotalRevenue();
$ordersCount = $orderModel->count();
$productsCount = $productModel->count();
$customersCount = $userModel->countCustomers();

$recentOrders = array_slice($orderModel->getAll(), 0, 8);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom">
    <div>
        <h1 class="h2 fw-bold mb-1">Dashboard Insights</h1>
        <p class="text-muted mb-0">Overview of operational metrics, sales volume, and order activity.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="<?= BASE_URL ?>admin/products/create.php" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add Product
        </a>
        <a href="<?= BASE_URL ?>admin/orders/index.php" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-list-check me-1"></i> Manage Orders
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="bg-success-subtle text-success rounded-3 p-3 me-3">
                    <i class="bi bi-currency-dollar fs-2"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold">Total Revenue (Paid)</span>
                    <h3 class="fw-bold mb-0 text-dark">$<?= number_format($totalRevenue, 2) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="bg-primary-subtle text-primary rounded-3 p-3 me-3">
                    <i class="bi bi-receipt fs-2"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold">Orders Count</span>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($ordersCount) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="bg-warning-subtle text-warning-emphasis rounded-3 p-3 me-3">
                    <i class="bi bi-box-seam fs-2"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold">Product Count</span>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($productsCount) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="bg-info-subtle text-info-emphasis rounded-3 p-3 me-3">
                    <i class="bi bi-people fs-2"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold">Customers</span>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($customersCount) ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-semibold">Recent Customer Orders</h5>
        <a href="<?= BASE_URL ?>admin/orders/index.php" class="btn btn-sm btn-outline-primary">View All Orders</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($recentOrders)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox display-4 text-muted"></i>
                <h5 class="mt-3 text-secondary">No orders recorded yet</h5>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">Order ID</th>
                            <th scope="col">Customer</th>
                            <th scope="col">Phone</th>
                            <th scope="col">Total</th>
                            <th scope="col">Payment</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td class="ps-3 fw-bold">#ORD-<?= str_pad((string)$order['id'], 6, '0', STR_PAD_LEFT) ?></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($order['shipping_phone']) ?></td>
                                <td class="fw-semibold text-primary">$<?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <span class="badge bg-<?= $order['payment_status'] === 'paid' ? 'success' : 'secondary' ?> text-capitalize">
                                        <?= htmlspecialchars($order['payment_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $order['order_status'] === 'delivered' ? 'success' : 'warning text-dark' ?> text-capitalize">
                                        <?= htmlspecialchars($order['order_status']) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="<?= BASE_URL ?>admin/orders/view.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye me-1"></i> Inspect
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>