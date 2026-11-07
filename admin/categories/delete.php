<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/Session.php';
require_once __DIR__ . '/../../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../../classes/Category.php';

AuthMiddleware::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id > 0) {
        $categoryModel = new Category();
        try {
            if ($categoryModel->delete($id)) {
                Session::setFlash('success', 'Category deleted successfully.');
            } else {
                Session::setFlash('error', 'Category could not be found or deleted.');
            }
        } catch (Throwable $e) {
            Session::setFlash('error', 'Cannot delete this category because it contains active dependencies.');
        }
    } else {
        Session::setFlash('error', 'Invalid category identifier.');
    }
}

header('Location: ' . BASE_URL . 'admin/categories/index.php');
exit;