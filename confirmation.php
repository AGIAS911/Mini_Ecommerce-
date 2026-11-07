<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/helpers/Session.php';
require_once __DIR__ . '/helpers/AuthMiddleware.php';
require_once __DIR__ . '/classes/Order.php';

AuthMiddleware::requireAuth();

$orderId = isset($_GET['order_id']) && is_numeric($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

$orderModel = new Order();
$order = $orderModel->getById($orderId);

if (!$order) {
    Session::setFlash('error', 'Order not found.');
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$currentUserId = (int)Session::get('user_id');
if ($order['user_id'] !== $currentUserId && !AuthMiddleware::isAdmin()) {
    Session::setFlash('error', 'Access denied.');
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$orderItems = $orderModel->getItems($orderId);

$pageTitle = 'Order Receipt #' . $order['id'] . ' - E-Shop';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle mb-3" style="width: 65px; height: 65px;">
                <i class="bi bi-check-lg fs-1"></i>
            </div>
            <h2 class="fw-bold">Thank You For Your Order!</h2>
            <p class="text-muted">Your order has been recorded and will be prepared for delivery.</p>
        </div>

        <div class="card border-0 shadow-sm" id="printableReceipt">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small">Order Reference:</span>
                    <h5 class="mb-0 fw-bold">#ORD-<?= str_pad((string)$order['id'], 6, '0', STR_PAD_LEFT) ?></h5>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-warning text-dark text-capitalize px-3 py-2">
                        <i class="bi bi-hourglass-split me-1"></i> <?= htmlspecialchars($order['order_status']) ?>
                    </span>
                    <span class="badge bg-secondary text-capitalize px-3 py-2">
                        <i class="bi bi-cash me-1"></i> <?= htmlspecialchars($order['payment_status']) ?>
                    </span>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row mb-4 g-3">
                    <div class="col-md-6">
                        <h6 class="text-muted text-uppercase small fw-bold mb-2">Customer Details</h6>
                        <p class="mb-1 fw-semibold text-dark"><?= htmlspecialchars($order['customer_name']) ?></p>
                        <p class="mb-1 text-muted small"><?= htmlspecialchars($order['customer_email']) ?></p>
                        <p class="mb-0 text-muted small">
                            <i class="bi bi-telephone me-1"></i> <?= htmlspecialchars($order['shipping_phone']) ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted text-uppercase small fw-bold mb-2">Shipping Information</h6>
                        <p class="mb-1 text-muted small leading-normal">
                            <i class="bi bi-geo-alt me-1"></i> <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
                        </p>
                        <p class="mb-0 text-muted small">
                            <strong>Payment:</strong> Cash on Delivery (COD)
                        </p>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 50%;">Item</th>
                                <th scope="col" class="text-center" style="width: 15%;">Unit Price</th>
                                <th scope="col" class="text-center" style="width: 15%;">Quantity</th>
                                <th scope="col" class="text-end" style="width: 20%;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded border bg-light d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 45px; height: 45px;">
                                                <?php if (!empty($item['image_path']) && file_exists(__DIR__ . '/' . $item['image_path'])): ?>
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
                                    <td class="text-center text-muted">$<?= number_format($item['price'], 2) ?></td>
                                    <td class="text-center"><?= $item['quantity'] ?></td>
                                    <td class="text-end fw-semibold text-dark">
                                        $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-semibold">Subtotal:</td>
                                <td class="text-end fw-semibold">$<?= number_format($order['total_amount'], 2) ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end fw-semibold">Shipping:</td>
                                <td class="text-end text-success fw-semibold">Free</td>
                            </tr>
                            <tr class="table-light">
                                <td colspan="3" class="text-end fw-bold fs-5">Amount to Pay on Delivery:</td>
                                <td class="text-end fw-bold fs-5 text-primary">$<?= number_format($order['total_amount'], 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="alert alert-light border small text-muted mb-0">
                    <i class="bi bi-info-circle me-1"></i> Please make sure to have the exact cash amount ready upon delivery.
                </div>
            </div>

            <div class="card-footer bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <a href="<?= BASE_URL ?>index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Continue Shopping
                </a>
                <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.print();">
                    <i class="bi bi-printer me-1"></i> Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>