<?php
$pageTitle = 'Manage Categories';
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../../classes/Category.php';

$categoryModel = new Category();
$categories = $categoryModel->getAll();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom">
    <div>
        <h1 class="h2 fw-bold mb-1">Categories</h1>
        <p class="text-muted mb-0">Organize store products into logical catalog classifications.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>admin/categories/create.php" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Category
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-semibold">All Categories (<?= count($categories) ?>)</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($categories)): ?>
            <div class="text-center py-5">
                <i class="bi bi-tags display-4 text-muted"></i>
                <h5 class="mt-3 text-secondary">No categories found</h5>
                <p class="text-muted small">Create your first category to start organizing products.</p>
                <a href="<?= BASE_URL ?>admin/categories/create.php" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Create Category
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3" style="width: 10%;">ID</th>
                            <th scope="col" style="width: 30%;">Name</th>
                            <th scope="col" style="width: 40%;">Description</th>
                            <th scope="col" class="text-end pe-3" style="width: 20%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td class="ps-3 fw-semibold text-secondary">#<?= $category['id'] ?></td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($category['name']) ?></td>
                                <td class="text-muted small">
                                    <?= htmlspecialchars($category['description'] ?? 'No description provided.') ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-2">
                                        <a href="<?= BASE_URL ?>admin/categories/edit.php?id=<?= $category['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </a>
                                        <form action="<?= BASE_URL ?>admin/categories/delete.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category? Associated products will be affected.');">
                                            <input type="hidden" name="id" value="<?= $category['id'] ?>">
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

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>s