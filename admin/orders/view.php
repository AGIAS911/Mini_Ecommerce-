<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/Session.php';
require_once __DIR__ . '/../../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../../classes/Order.php';

AuthMiddleware::requireAdmin();

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

$orderModel = new Order();
$order = $orderModel->getById($id);

if (!$order) {
    Session::setFlash('error', 'Order record not found.');
    header('Location: ' . BASE_URL . 'admin/orders/index.php');
    exit;
}

$orderItems = $orderModel->getItems($id);

$pageTitle = 'Order #ORD-' . str_pad((string)$order['id'], 6, '0', STR_PAD_LEFT);
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom">
    <div>
        <h1 class="h2 fw-bold mb-1">Order #ORD-<?= str_pad((string)$order['id'], 6, '0', STR_PAD_LEFT) ?></h1>
        <p class="text-muted mb-0">Review shipping specifics, line items, and adjust operational statuses.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="<?= BASE_URL ?>admin/orders/index.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Orders
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-semibold">Order Items (<?= count($orderItems) ?>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-3" style="width: 50%;">Product</th>
                                <th scope="col" class="text-center" style="width: 15%;">Unit Price</th>
                                <th scope="col" class="text-center" style="width: 15%;">Quantity</th>
                                <th scope="col" class="text-end pe-3" style="width: 20%;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded border bg-light d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 48px; height: 48px;">
                                                <?php if (!empty($item['image_path']) && file_exists(__DIR__ . '/../../' . $item['image_path'])): ?>
                                                    <img src="<?= BASE_URL . htmlspecialchars($item['image_path']) ?>" 
                                                         alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                                         class="img-fluid rounded object-fit-cover w-100 h-100">
                                                <?php else: ?>
                                                    <i class="bi bi-image text-muted small"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <span class="fw-semibold text-dark d-block">
                                                    <?= htmlspecialchars($item['product_name']) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center text-secondary">$<?= number_format($item['price'], 2) ?></td>
                                    <td class="text-center fw-semibold"><?= $item['quantity'] ?></td>
                                    <td class="text-end pe-3 fw-bold text-dark">
                                        $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-semibold">Subtotal:</td>
                                <td class="text-end pe-3 fw-semibold">$<?= number_format($order['total_amount'], 2) ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end fw-semibold">Delivery Fee:</td>
                                <td class="text-end pe-3 text-success fw-semibold">Free</td>
                            </tr>
                            <tr class="table-light">
                                <td colspan="3" class="text-end fw-bold fs-5">Total Cash on Delivery:</td>
                                <td class="text-end pe-3 fw-bold fs-5 text-primary">$<?= number_format($order['total_amount'], 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-semibold">Customer & Delivery Details</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold text-uppercase d-block mb-1">Customer Name</label>
                        <p class="fw-semibold text-dark mb-3"><?= htmlspecialchars($order['customer_name']) ?></p>

                        <label class="text-muted small fw-bold text-uppercase d-block mb-1">Email Address</label>
                        <p class="text-muted mb-0"><?= htmlspecialchars($order['customer_email']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold text-uppercase d-block mb-1">Contact Phone</label>
                        <p class="fw-semibold text-dark mb-3">
                            <i class="bi bi-telephone me-1"></i> <?= htmlspecialchars($order['shipping_phone']) ?>
                        </p>

                        <label class="text-muted small fw-bold text-uppercase d-block mb-1">Destination Address</label>
                        <p class="text-muted mb-0">
                            <i class="bi bi-geo-alt me-1"></i> <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-semibold">Update Status</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>admin/orders/update_status.php" method="POST">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

                    <div class="mb-3">
                        <label for="order_status" class="form-label fw-semibold small">Fulfillment Status</label>
                        <select name="order_status" id="order_status" class="form-select">
                            <option value="pending" <?= $order['order_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="delivered" <?= $order['order_status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="payment_status" class="form-label fw-semibold small">Payment Collection (COD)</label>
                        <select name="payment_status" id="payment_status" class="form-select">
                            <option value="unpaid" <?= $order['payment_status'] === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                            <option value="paid" <?= $order['payment_status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-semibold">
                        Update Order
                    </button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-2">Payment Terms</h6>
                <p class="small text-muted mb-0">
                    This order was finalized under <strong>Cash on Delivery (COD)</strong> terms. Marking payment as <strong>Paid</strong> records the cash receipt and updates the total revenue on your dashboard.
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>s