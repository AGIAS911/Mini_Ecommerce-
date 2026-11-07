<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/helpers/Session.php';
require_once __DIR__ . '/classes/Cart.php';

$cart = new Cart();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    if ($action === 'add') {
        if ($productId > 0) {
            if ($cart->add($productId, $quantity)) {
                Session::setFlash('success', 'Product added to cart successfully.');
            } else {
                Session::setFlash('error', 'Unable to add product. Requested quantity exceeds stock.');
            }
        }
        header('Location: ' . BASE_URL . 'cart.php');
        exit;
    }

    if ($action === 'update') {
        if ($productId > 0) {
            if ($cart->update($productId, $quantity)) {
                Session::setFlash('success', 'Cart updated successfully.');
            } else {
                Session::setFlash('error', 'Unable to update item. Quantity exceeds available stock.');
            }
        }
        header('Location: ' . BASE_URL . 'cart.php');
        exit;
    }

    if ($action === 'remove') {
        if ($productId > 0) {
            $cart->remove($productId);
            Session::setFlash('success', 'Item removed from your cart.');
        }
        header('Location: ' . BASE_URL . 'cart.php');
        exit;
    }

    if ($action === 'clear') {
        $cart->clear();
        Session::setFlash('success', 'Cart cleared successfully.');
        header('Location: ' . BASE_URL . 'cart.php');
        exit;
    }
}

$pageTitle = 'Shopping Cart - E-Shop';
require_once __DIR__ . '/includes/header.php';

$items = $cart->getItems();
$total = $cart->getTotal();
?>

<div class="row mb-4">
    <div class="col">
        <h1 class="h3 fw-bold">Shopping Cart</h1>
        <p class="text-muted mb-0">Review your selected items before proceeding to cash on delivery checkout.</p>
    </div>
</div>

<?php if (empty($items)): ?>
    <div class="card border-0 shadow-sm text-center py-5">
        <div class="card-body">
            <i class="bi bi-cart-x display-1 text-muted"></i>
            <h4 class="mt-4 fw-semibold">Your cart is currently empty</h4>
            <p class="text-muted">Explore our catalog and find the products you need.</p>
            <a href="<?= BASE_URL ?>index.php" class="btn btn-primary px-4 mt-2">
                <i class="bi bi-arrow-left me-1"></i> Continue Shopping
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Items in Cart (<?= count($items) ?>)</span>
                    <form action="<?= BASE_URL ?>cart.php" method="POST" onsubmit="return confirm('Clear entire cart?');">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-trash3 me-1"></i> Clear Cart
                        </button>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 45%;">Product</th>
                                <th scope="col" style="width: 15%;">Price</th>
                                <th scope="col" style="width: 20%;">Quantity</th>
                                <th scope="col" style="width: 15%;">Subtotal</th>
                                <th scope="col" style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded border bg-light d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 60px; height: 60px;">
                                                <?php if (!empty($item['image_path']) && file_exists(__DIR__ . '/' . $item['image_path'])): ?>
                                                    <img src="<?= BASE_URL . htmlspecialchars($item['image_path']) ?>" 
                                                         alt="<?= htmlspecialchars($item['name']) ?>" 
                                                         class="img-fluid rounded object-fit-cover w-100 h-100">
                                                <?php else: ?>
                                                    <i class="bi bi-image text-muted"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <a href="<?= BASE_URL ?>product_details.php?id=<?= $item['product_id'] ?>" class="text-decoration-none text-dark fw-semibold d-block">
                                                    <?= htmlspecialchars($item['name']) ?>
                                                </a>
                                                <small class="text-muted">Stock: <?= $item['stock'] ?> units</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fw-semibold text-secondary">
                                        $<?= number_format($item['price'], 2) ?>
                                    </td>
                                    <td>
                                        <form action="<?= BASE_URL ?>cart.php" method="POST" class="d-flex align-items-center gap-1">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                            <input type="number" 
                                                   name="quantity" 
                                                   value="<?= $item['quantity'] ?>" 
                                                   min="1" 
                                                   max="<?= $item['stock'] ?>" 
                                                   class="form-control form-control-sm text-center" 
                                                   style="width: 65px;">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Update Quantity">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="fw-bold text-primary">
                                        $<?= number_format($item['subtotal'], 2) ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <form action="<?= BASE_URL ?>cart.php" method="POST" onsubmit="return confirm('Remove this product?');">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-light text-danger border-0" title="Remove Item">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-3">
                <a href="<?= BASE_URL ?>index.php" class="text-decoration-none small fw-semibold">
                    <i class="bi bi-arrow-left me-1"></i> Continue Shopping
                </a>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-semibold">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-semibold">$<?= number_format($total, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Shipping</span>
                        <span class="text-success fw-semibold">Free Delivery</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fs-5 fw-bold">Total</span>
                        <span class="fs-4 fw-bold text-primary">$<?= number_format($total, 2) ?></span>
                    </div>

                    <div class="alert alert-light border small text-muted mb-4">
                        <i class="bi bi-cash-stack text-success me-1"></i> Payment Method: <strong>Cash on Delivery (COD)</strong>
                    </div>

                    <div class="d-grid">
                        <a href="<?= BASE_URL ?>checkout.php" class="btn btn-primary py-2 fw-semibold">
                            Proceed to Checkout <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>