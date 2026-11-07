<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/Session.php';
require_once __DIR__ . '/../../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../../helpers/FileUploader.php';
require_once __DIR__ . '/../../classes/Product.php';
require_once __DIR__ . '/../../classes/Category.php';

AuthMiddleware::requireAdmin();

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

$productModel = new Product();
$product = $productModel->getById($id);

if (!$product) {
    Session::setFlash('error', 'Product not found.');
    header('Location: ' . BASE_URL . 'admin/products/index.php');
    exit;
}

$categoryModel = new Category();
$categories = $categoryModel->getAll();

$errors = [];
$name = $product['name'];
$categoryId = $product['category_id'];
$price = $product['price'];
$stock = $product['stock'];
$description = $product['description'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $categoryId = isset($_POST['category_id']) && is_numeric($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $price = trim($_POST['price'] ?? '');
    $stock = trim($_POST['stock'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $errors['name'] = 'Product name is required.';
    }

    if ($categoryId <= 0) {
        $errors['category_id'] = 'Please select a valid category.';
    }

    if ($price === '' || !is_numeric($price) || (float)$price < 0) {
        $errors['price'] = 'A valid positive price is required.';
    }

    if ($stock === '' || !ctype_digit((string)$stock)) {
        $errors['stock'] = 'Stock must be a non-negative integer.';
    }

    $uploadedImagePath = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        try {
            $uploadedImagePath = FileUploader::upload($_FILES['image']);
        } catch (Throwable $e) {
            $errors['image'] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        try {
            $descToSave = !empty($description) ? $description : null;
            $oldImagePath = $product['image_path'];

            $productModel->update(
                $id,
                $categoryId,
                $name,
                $descToSave,
                (float)$price,
                (int)$stock,
                $uploadedImagePath
            );

            if ($uploadedImagePath !== null && !empty($oldImagePath)) {
                FileUploader::delete($oldImagePath);
            }

            Session::setFlash('success', 'Product updated successfully.');
            header('Location: ' . BASE_URL . 'admin/products/index.php');
            exit;
        } catch (Throwable $e) {
            if ($uploadedImagePath !== null) {
                FileUploader::delete($uploadedImagePath);
            }
            $errors['general'] = 'Failed to update product: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Edit Product #' . $id;
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom">
    <div>
        <h1 class="h2 fw-bold mb-1">Edit Product</h1>
        <p class="text-muted mb-0">Modify specifications and manage media assets for product #<?= $id ?></p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>admin/products/index.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Products
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-semibold">Product Specifications</h5>
            </div>
            <div class="card-body p-4">
                <?php if (isset($errors['general'])): ?>
                    <div class="alert alert-danger py-2 small" role="alert">
                        <i class="bi bi-exclamation-circle me-1"></i> <?= htmlspecialchars($errors['general']) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>admin/products/edit.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold small">Product Name <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                               id="name" 
                               name="name" 
                               value="<?= htmlspecialchars($name) ?>" 
                               required>
                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="category_id" class="form-label fw-semibold small">Category <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>" 
                                    id="category_id" 
                                    name="category_id" 
                                    required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= (string)$categoryId === (string)$cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['category_id'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['category_id']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-3">
                            <label for="price" class="form-label fw-semibold small">Price ($) <span class="text-danger">*</span></label>
                            <input type="number" 
                                   step="0.01" 
                                   min="0" 
                                   class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>" 
                                   id="price" 
                                   name="price" 
                                   value="<?= htmlspecialchars((string)$price) ?>" 
                                   required>
                            <?php if (isset($errors['price'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['price']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-3">
                            <label for="stock" class="form-label fw-semibold small">Stock <span class="text-danger">*</span></label>
                            <input type="number" 
                                   min="0" 
                                   class="form-control <?= isset($errors['stock']) ? 'is-invalid' : '' ?>" 
                                   id="stock" 
                                   name="stock" 
                                   value="<?= htmlspecialchars((string)$stock) ?>" 
                                   required>
                            <?php if (isset($errors['stock'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['stock']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small d-block">Current Image</label>
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="rounded border bg-light d-flex align-items-center justify-content-center overflow-hidden" style="width: 70px; height: 70px;">
                                <?php if (!empty($product['image_path']) && file_exists(__DIR__ . '/../../' . $product['image_path'])): ?>
                                    <img src="<?= BASE_URL . htmlspecialchars($product['image_path']) ?>" 
                                         alt="<?= htmlspecialchars($product['name']) ?>" 
                                         class="img-fluid object-fit-cover w-100 h-100">
                                <?php else: ?>
                                    <i class="bi bi-image text-muted fs-4"></i>
                                <?php endif; ?>
                            </div>
                            <span class="text-muted small">
                                <?= !empty($product['image_path']) ? htmlspecialchars($product['image_path']) : 'No primary image assigned.' ?>
                            </span>
                        </div>

                        <label for="image" class="form-label fw-semibold small">Replace Image (JPG, PNG, WEBP, Max 2MB)</label>
                        <input type="file" 
                               class="form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>" 
                               id="image" 
                               name="image" 
                               accept="image/jpeg,image/png,image/webp">
                        <?php if (isset($errors['image'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['image']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold small">Description</label>
                        <textarea class="form-control" 
                                  id="description" 
                                  name="description" 
                                  rows="4"><?= htmlspecialchars($description) ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= BASE_URL ?>admin/products/index.php" class="btn btn-light border px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>