<?php
$pageTitle = 'Manage Products';
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../../classes/Product.php';
require_once __DIR__ . '/../../classes/Category.php';

$productModel = new Product();
$categoryModel = new Category();

$selectedCategory = isset($_GET['category']) && is_numeric($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : null;

$products = $productModel->getAll($selectedCategory, $search);
$categories = $categoryModel->getAll();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom">
    <div>
        <h1 class="h2 fw-bold mb-1">Products</h1>
        <p class="text-muted mb-0">Manage catalog merchandise, stock counts, and product imagery.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>admin/products/create.php" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Product
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="<?= BASE_URL ?>admin/products/index.php" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by product name..." value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
            </div>
            <div class="col-md-4">
                <select name="category" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $selectedCategory === (int)$cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-dark w-100">Filter</button>
                <?php if ($selectedCategory !== null || !empty($search)): ?>
                    <a href="<?= BASE_URL ?>admin/products/index.php" class="btn btn-sm btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-semibold">Product Catalog (<?= count($products) ?>)</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($products)): ?>
            <div class="text-center py-5">
                <i class="bi bi-box-seam display-4 text-muted"></i>
                <h5 class="mt-3 text-secondary">No products found</h5>
                <p class="text-muted small">Try different filter criteria or add a new product to your inventory.</p>
                <a href="<?= BASE_URL ?>admin/products/create.php" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Create Product
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3" style="width: 80px;">Thumbnail</th>
                            <th scope="col">Name</th>
                            <th scope="col">Category</th>
                            <th scope="col">Price</th>
                            <th scope="col">Stock</th>
                            <th scope="col" class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="rounded border bg-light d-flex align-items-center justify-content-center overflow-hidden" style="width: 50px; height: 50px;">
                                        <?php if (!empty($product['image_path']) && file_exists(__DIR__ . '/../../' . $product['image_path'])): ?>
                                            <img src="<?= BASE_URL . htmlspecialchars($product['image_path']) ?>" 
                                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                                 class="img-fluid object-fit-cover w-100 h-100">
                                        <?php else: ?>
                                            <i class="bi bi-image text-muted small"></i>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark d-block"><?= htmlspecialchars($product['name']) ?></span>
                                    <small class="text-muted text-truncate d-inline-block" style="max-width: 260px;">
                                        <?= htmlspecialchars($product['description'] ?? 'No description.') ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= htmlspecialchars($product['category_name'] ?? 'Unassigned') ?>
                                    </span>
                                </td>
                                <td class="fw-bold text-primary">
                                    $<?= number_format($product['price'], 2) ?>
                                </td>
                                <td>
                                    <?php if ($product['stock'] <= 0): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Out of Stock</span>
                                    <?php elseif ($product['stock'] < 5): ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><?= $product['stock'] ?> left</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle"><?= $product['stock'] ?> units</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-2">
                                        <a href="<?= BASE_URL ?>admin/products/edit.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </a>
                                        <form action="<?= BASE_URL ?>admin/products/delete.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product? All image files and associations will be permanently removed.');">
                                            <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash me-1"></i> Delete
                                            </button>
                                        </form>
                                    </div>
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