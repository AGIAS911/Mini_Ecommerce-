<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/helpers/Session.php';
require_once __DIR__ . '/helpers/AuthMiddleware.php';
require_once __DIR__ . '/classes/Cart.php';
require_once __DIR__ . '/classes/Order.php';

AuthMiddleware::requireAuth();

$cart = new Cart();
if ($cart->isEmpty()) {
    Session::setFlash('error', 'Your cart is empty. Please add items before checking out.');
    header('Location: ' . BASE_URL . 'cart.php');
    exit;
}

$items = $cart->getItems();
$total = $cart->getTotal();
$userId = (int)Session::get('user_id');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shippingAddress = trim($_POST['shipping_address'] ?? '');
    $shippingPhone = trim($_POST['shipping_phone'] ?? '');

    if (empty($shippingAddress)) {
        $errors['shipping_address'] = 'Shipping address is required.';
    }

    if (empty($shippingPhone)) {
        $errors['shipping_phone'] = 'Shipping phone number is required.';
    }

    if (empty($errors)) {
        try {
            $orderModel = new Order();
            $orderId = $orderModel->create($userId, $shippingAddress, $shippingPhone, $items);
            $cart->clear();

            Session::setFlash('success', 'Order placed successfully!');
            header('Location: ' . BASE_URL . 'confirmation.php?order_id=' . $orderId);
            exit;
        } catch (Throwable $e) {
            $errors['general'] = $e->getMessage();
        }
    }
}

$pageTitle = 'Checkout - E-Shop';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h1 class="h3 fw-bold">Checkout</h1>
        <p class="text-muted mb-0">Provide your shipping details to complete your order via Cash on Delivery.</p>
    </div>
</div>

<?php if (isset($errors['general'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($errors['general']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-semibold">Delivery Information</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>checkout.php" method="POST" id="checkoutForm">
                    <div class="mb-3">
                        <label for="shipping_phone" class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" 
                               class="form-control <?= isset($errors['shipping_phone']) ? 'is-invalid' : '' ?>" 
                               id="shipping_phone" 
                               name="shipping_phone" 
                               placeholder="e.g. 0791234567"
                               value="<?= htmlspecialchars($_POST['shipping_phone'] ?? '') ?>" 
                               required>
                        <?php if (isset($errors['shipping_phone'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['shipping_phone']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label for="shipping_address" class="form-label fw-semibold">Delivery Address <span class="text-danger">*</span></label>
                        <textarea class="form-control <?= isset($errors['shipping_address']) ? 'is-invalid' : '' ?>" 
                                  id="shipping_address" 
                                  name="shipping_address" 
                                  rows="3" 
                                  placeholder="City, Street name, Building No., Apartment No." 
                                  required><?= htmlspecialchars($_POST['shipping_address'] ?? '') ?></textarea>
                        <?php if (isset($errors['shipping_address'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['shipping_address']) ?></div>
                        <?php endif; ?>
                    </div>

                    <h5 class="fw-semibold mb-3">Payment Method</h5>
                    <div class="border rounded p-3 mb-4 bg-light">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="codPayment" value="cod" checked>
                            <label class="form-check-label fw-semibold text-dark" for="codPayment">
                                <i class="bi bi-cash-stack text-success me-1"></i> Cash on Delivery (COD)
                            </label>
                            <p class="small text-muted mb-0 mt-1">
                                Pay with cash when your package is handed over to you at your doorstep.
                            </p>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold">
                        Confirm & Place Order ($<?= number_format($total, 2) ?>)
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-semibold">Order Summary (<?= count($items) ?> items)</h5>
            </div>
            <div class="card-body p-4">
                <ul class="list-group list-group-flush mb-3">
                    <?php foreach ($items as $item): ?>
                        <li class="list-group-item px-0 py-2 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="rounded border bg-light d-flex align-items-center justify-content-center me-2 flex-shrink-0" style="width: 45px; height: 45px;">
                                    <?php if (!empty($item['image_path']) && file_exists(__DIR__ . '/' . $item['image_path'])): ?>
                                        <img src="<?= BASE_URL . htmlspecialchars($item['image_path']) ?>" 
                                             alt="<?= htmlspecialchars($item['name']) ?>" 
                                             class="img-fluid rounded object-fit-cover w-100 h-100">
                                    <?php else: ?>
                                        <i class="bi bi-image text-muted small"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h6 class="my-0 small fw-semibold text-truncate" style="max-width: 170px;">
                                        <?= htmlspecialchars($item['name']) ?>
                                    </h6>
                                    <small class="text-muted">Qty: <?= $item['quantity'] ?> × $<?= number_format($item['price'], 2) ?></small>
                                </div>
                            </div>
                            <span class="fw-semibold text-secondary small">$<?= number_format($item['subtotal'], 2) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <hr>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal</span>
                    <span class="fw-semibold">$<?= number_format($total, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Shipping</span>
                    <span class="text-success fw-semibold">Free</span>
                </div>
                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                    <span class="fs-5 fw-bold">Total</span>
                    <span class="fs-4 fw-bold text-primary">$<?= number_format($total, 2) ?></span>
                </div>
            </div>
        </div>

        <div class="mt-3 text-center">
            <a href="<?= BASE_URL ?>cart.php" class="text-decoration-none small text-muted">
                <i class="bi bi-pencil-square me-1"></i> Edit Cart Items
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>