<?php
$currentScript = $_SERVER['SCRIPT_NAME'];
?>
<nav id="adminSidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-white sidebar collapse border-end shadow-sm">
    <div class="position-sticky pt-3 sidebar-sticky">
        <ul class="nav flex-column gap-1">
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 <?= str_ends_with($currentScript, 'admin/index.php') ? 'active bg-primary text-white rounded' : 'text-dark' ?>" href="<?= BASE_URL ?>admin/index.php">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 <?= str_contains($currentScript, 'admin/categories/') ? 'active bg-primary text-white rounded' : 'text-dark' ?>" href="<?= BASE_URL ?>admin/categories/index.php">
                    <i class="bi bi-tags"></i>
                    <span>Categories</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 <?= str_contains($currentScript, 'admin/products/') ? 'active bg-primary text-white rounded' : 'text-dark' ?>" href="<?= BASE_URL ?>admin/products/index.php">
                    <i class="bi bi-box-seam"></i>
                    <span>Products</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 <?= str_contains($currentScript, 'admin/orders/') ? 'active bg-primary text-white rounded' : 'text-dark' ?>" href="<?= BASE_URL ?>admin/orders/index.php">
                    <i class="bi bi-receipt"></i>
                    <span>Orders</span>
                </a>
            </li>
        </ul>

        <hr class="my-3">

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-2 text-muted text-uppercase small">
            <span>Quick Shortcuts</span>
        </h6>
        <ul class="nav flex-column mb-auto gap-1">
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 text-secondary" href="<?= BASE_URL ?>admin/products/create.php">
                    <i class="bi bi-plus-circle"></i>
                    <span>New Product</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 text-secondary" href="<?= BASE_URL ?>admin/categories/create.php">
                    <i class="bi bi-folder-plus"></i>
                    <span>New Category</span>
                </a>
            </li>
        </ul>
    </div>
</nav>