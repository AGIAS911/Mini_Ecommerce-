<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/Session.php';
require_once __DIR__ . '/../../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../../classes/Category.php';

AuthMiddleware::requireAdmin();

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

$categoryModel = new Category();
$category = $categoryModel->getById($id);

if (!$category) {
    Session::setFlash('error', 'Category not found.');
    header('Location: ' . BASE_URL . 'admin/categories/index.php');
    exit;
}

$errors = [];
$name = $category['name'];
$description = $category['description'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $errors['name'] = 'Category name is required.';
    } elseif ($categoryModel->nameExists($name, $id)) {
        $errors['name'] = 'A category with this name already exists.';
    }

    if (empty($errors)) {
        $descToSave = !empty($description) ? $description : null;
        if ($categoryModel->update($id, $name, $descToSave)) {
            Session::setFlash('success', 'Category updated successfully.');
            header('Location: ' . BASE_URL . 'admin/categories/index.php');
            exit;
        } else {
            $errors['general'] = 'Failed to update category. Please try again.';
        }
    }
}

$pageTitle = 'Edit Category #' . $id;
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom">
    <div>
        <h1 class="h2 fw-bold mb-1">Edit Category</h1>
        <p class="text-muted mb-0">Modify information for category: <?= htmlspecialchars($category['name']) ?></p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>admin/categories/index.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Categories
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-semibold">Edit Details</h5>
            </div>
            <div class="card-body p-4">
                <?php if (isset($errors['general'])): ?>
                    <div class="alert alert-danger py-2 small" role="alert">
                        <i class="bi bi-exclamation-circle me-1"></i> <?= htmlspecialchars($errors['general']) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>admin/categories/edit.php?id=<?= $id ?>" method="POST" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold small">Category Name <span class="text-danger">*</span></label>
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

                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold small">Description</label>
                        <textarea class="form-control" 
                                  id="description" 
                                  name="description" 
                                  rows="4"><?= htmlspecialchars($description) ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= BASE_URL ?>admin/categories/index.php" class="btn btn-light border px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>