<?php
$pageTitle = 'Product Catalog - E-Shop';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/Product.php';
require_once __DIR__ . '/classes/Category.php';

$productModel = new Product();
$categoryModel = new Category();

$categories = $categoryModel->getAll();

$selectedCategory = isset($_GET['category']) && is_numeric($_GET['category']) ? (int)$_GET['category'] : null;
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : null;

$products = $productModel->getAll($selectedCategory, $searchQuery);
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h1 class="h3 fw-bold mb-1">Catalog</h1>
        <p class="text-muted mb-0">Browse our latest collection and shop with cash on delivery.</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <span class="badge bg-secondary fs-6"><?= count($products) ?> Items Available</span>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-semibold">Categories</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="<?= BASE_URL ?>index.php<?= !empty($searchQuery) ? '?search=' . urlencode($searchQuery) : '' ?>" 
                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $selectedCategory === null ? 'active' : '' ?>">
                    All Categories
                    <i class="bi bi-chevron-right small"></i>
                </a>
                <?php foreach ($categories as $category): ?>
                    <?php
                    $url = BASE_URL . 'index.php?category=' . $category['id'];
                    if (!empty($searchQuery)) {
                        $url .= '&search=' . urlencode($searchQuery);
                    }
                    ?>
                    <a href="<?= $url ?>" 
                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $selectedCategory === (int)$category['id'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($category['name']) ?>
                        <i class="bi bi-chevron-right small"></i>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($selectedCategory !== null || !empty($searchQuery)): ?>
            <div class="d-grid mt-3">
                <a href="<?= BASE_URL ?>index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-circle me-1"></i> Reset Filters
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-9">
        <?php if (!empty($searchQuery)): ?>
            <div class="alert alert-info py-2 d-flex justify-content-between align-items-center mb-4">
                <span>Search results for: <strong><?= htmlspecialchars($searchQuery) ?></strong></span>
                <a href="<?= BASE_URL ?>index.php<?= $selectedCategory !== null ? '?category=' . $selectedCategory : '' ?>" class="text-decoration-none small text-dark fw-bold">Clear</a>
            </div>
        <?php endif; ?>

        <?php if (empty($products)): ?>
            <div class="card border-0 shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="bi bi-box-seam display-1 text-muted"></i>
                    <h4 class="mt-3">No Products Found</h4>
                    <p class="text-muted">Try adjusting your search criteria or selecting a different category.</p>
                    <a href="<?= BASE_URL ?>index.php" class="btn btn-primary btn-sm">Browse All Products</a>
                </div>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
                <?php foreach ($products as $product): ?>
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm product-card">
                            <div class="ratio ratio-4x3 bg-light position-relative overflow-hidden rounded-top">
                                <?php if (!empty($product['image_path']) && file_exists(__DIR__ . '/' . $product['image_path'])): ?>
                                    <img src="<?= BASE_URL . htmlspecialchars($product['image_path']) ?>" 
                                         class="card-img-top object-fit-cover" 
                                         alt="<?= htmlspecialchars($product['name']) ?>">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center bg-light text-muted">
                                        <i class="bi bi-image fs-1"></i>
                                    </div>
                                <?php endif; ?>

                                <?php if ($product['stock'] <= 0): ?>
                                    <span class="position-absolute top-0 end-0 badge bg-danger m-2">Out of Stock</span>
                                <?php endif; ?>
                            </div>

                            <div class="card-body d-flex flex-column">
                                <span class="badge bg-light text-dark align-self-start mb-2 border">
                                    <?= htmlspecialchars($product['category_name'] ?? 'General') ?>
                                </span>
                                <h5 class="card-title fs-6 fw-bold mb-1 text-truncate">
                                    <a href="<?= BASE_URL ?>product_details.php?id=<?= $product['id'] ?>" class="text-decoration-none text-dark">
                                        <?= htmlspecialchars($product['name']) ?>
                                    </a>
                                </h5>
                                <p class="card-text text-muted small flex-grow-1 text-truncate-2">
                                    <?= htmlspecialchars($product['description'] ?? '') ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                    <span class="fs-5 fw-bold text-primary">$<?= number_format($product['price'], 2) ?></span>
                                    <a href="<?= BASE_URL ?>product_details.php?id=<?= $product['id'] ?>" class="btn btn-outline-primary btn-sm">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>