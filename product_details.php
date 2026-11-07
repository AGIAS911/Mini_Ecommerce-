<?php
$pageTitle = 'Product Details - E-Shop';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/Product.php';

$productId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

$productModel = new Product();
$product = $productModel->getById($productId);

if (!$product) {
    Session::setFlash('error', 'The requested product was not found.');
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$images = $productModel->getImages($productId);
?>

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php" class="text-decoration-none">Catalog</a></li>
        <li class="breadcrumb-item">
            <a href="<?= BASE_URL ?>index.php?category=<?= $product['category_id'] ?>" class="text-decoration-none">
                <?= htmlspecialchars($product['category_name'] ?? 'Category') ?>
            </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
    </ol>
</nav>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="row g-5">
            <div class="col-md-6">
                <div class="ratio ratio-1x1 bg-light rounded overflow-hidden border mb-3">
                    <?php if (!empty($product['image_path']) && file_exists(__DIR__ . '/' . $product['image_path'])): ?>
                        <img id="mainProductImage" 
                             src="<?= BASE_URL . htmlspecialchars($product['image_path']) ?>" 
                             class="object-fit-contain w-100 h-100 p-2" 
                             alt="<?= htmlspecialchars($product['name']) ?>">
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center text-muted w-100 h-100">
                            <i class="bi bi-image fs-1"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (count($images) > 1): ?>
                    <div class="d-flex gap-2 overflow-x-auto pb-2">
                        <?php foreach ($images as $img): ?>
                            <?php if (file_exists(__DIR__ . '/' . $img['image_path'])): ?>
                                <button type="button" 
                                        class="btn p-0 border rounded thumbnail-btn <?= $img['image_path'] === $product['image_path'] ? 'border-primary border-2' : '' ?>" 
                                        onclick="document.getElementById('mainProductImage').src='<?= BASE_URL . htmlspecialchars($img['image_path']) ?>';">
                                    <img src="<?= BASE_URL . htmlspecialchars($img['image_path']) ?>" 
                                         class="rounded object-fit-cover" 
                                         style="width: 70px; height: 70px;" 
                                         alt="Product thumbnail">
                                </button>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-6 d-flex flex-column">
                <div class="mb-2">
                    <span class="badge bg-secondary"><?= htmlspecialchars($product['category_name'] ?? 'General') ?></span>
                </div>
                
                <h1 class="h2 fw-bold text-dark mb-3"><?= htmlspecialchars($product['name']) ?></h1>

                <div class="mb-4">
                    <span class="display-6 fw-bold text-primary">$<?= number_format($product['price'], 2) ?></span>
                </div>

                <div class="mb-4">
                    <div class="d-flex align-items-center">
                        <span class="fw-semibold me-2">Availability:</span>
                        <?php if ($product['stock'] > 0): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                <i class="bi bi-check-circle-fill me-1"></i> In Stock (<?= $product['stock'] ?> units)
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                <i class="bi bi-x-circle-fill me-1"></i> Out of Stock
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-4">
                    <h5 class="fw-semibold">Description</h5>
                    <p class="text-muted leading-relaxed">
                        <?= nl2br(htmlspecialchars($product['description'] ?? 'No description provided.')) ?>
                    </p>
                </div>

                <div class="mt-auto pt-4 border-top">
                    <?php if ($product['stock'] > 0): ?>
                        <form action="<?= BASE_URL ?>cart.php" method="POST" class="row g-3 align-items-center">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                            <div class="col-auto">
                                <label for="quantity" class="visually-hidden">Quantity</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">Qty</span>
                                    <input type="number" 
                                           class="form-control text-center" 
                                           id="quantity" 
                                           name="quantity" 
                                           value="1" 
                                           min="1" 
                                           max="<?= $product['stock'] ?>" 
                                           style="width: 80px;">
                                </div>
                            </div>

                            <div class="col">
                                <button type="submit" class="btn btn-primary w-100 py-2">
                                    <i class="bi bi-cart-plus me-2"></i> Add to Cart
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-secondary w-100 py-2" disabled>
                            <i class="bi bi-slash-circle me-2"></i> Unavailable
                        </button>
                    <?php endif; ?>

                    <div class="alert alert-light border mt-3 mb-0 small text-muted">
                        <i class="bi bi-truck me-1"></i> Eligible for <strong>Cash on Delivery (COD)</strong> at checkout.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>