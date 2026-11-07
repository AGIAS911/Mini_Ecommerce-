<?php
$pageTitle = 'Manage Orders';
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../../classes/Order.php';

$orderModel = new Order();
$orders = $orderModel->getAll();

$statusFilter = $_GET['status'] ?? '';
$paymentFilter = $_GET['payment'] ?? '';

if (!empty($statusFilter) || !empty($paymentFilter)) {
    $orders = array_filter($orders, function ($order) use ($statusFilter, $paymentFilter) {
        $matchStatus = empty($statusFilter) || $order['order_status'] === $statusFilter;
        $matchPayment = empty($paymentFilter) || $order['payment_status'] === $paymentFilter;
        return $matchStatus && $matchPayment;
    });
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom">
    <div>
        <h1 class="h2 fw-bold mb-1">Orders</h1>
        <p class="text-muted mb-0">Track shipments, verify cash collection, and update delivery statuses.</p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="<?= BASE_URL ?>admin/orders/index.php" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Order Statuses</option>
                    <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="delivered" <?= $statusFilter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                </select>
            </div>
            <div class="col-md-4">
                <select name="payment" class="form-select form-select-sm">
                    <option value="">All Payment Statuses</option>
                    <option value="unpaid" <?= $paymentFilter === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                    <option value="paid" <?= $paymentFilter === 'paid' ? 'selected' : '' ?>>Paid</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-dark w-100">Filter Orders</button>
                <?php if (!empty($statusFilter) || !empty($paymentFilter)): ?>
                    <a href="<?= BASE_URL ?>admin/orders/index.php" class="btn btn-sm btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-semibold">Order Records (<?= count($orders) ?>)</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($orders)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox display-4 text-muted"></i>
                <h5 class="mt-3 text-secondary">No orders match the criteria</h5>
                <p class="text-muted small">Orders placed via cash on delivery will be listed here.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">Order ID</th>
                            <th scope="col">Customer</th>
                            <th scope="col">Phone</th>
                            <th scope="col">Total Amount</th>
                            <th scope="col">Payment</th>
                            <th scope="col">Order Status</th>
                            <th scope="col" class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="ps-3 fw-bold">
                                    #ORD-<?= str_pad((string)$order['id'], 6, '0', STR_PAD_LEFT) ?>
                                </td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($order['shipping_phone']) ?></td>
                                <td class="fw-bold text-primary">$<?= number_format($order['total_amount'], 2) ?></td>
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
                                    <a href="<?= BASE_URL ?>admin/orders/view.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i> View Details
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

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>