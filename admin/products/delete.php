<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/Session.php';
require_once __DIR__ . '/../../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../../helpers/FileUploader.php';
require_once __DIR__ . '/../../classes/Product.php';

AuthMiddleware::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id > 0) {
        $productModel = new Product();
        $images = $productModel->getImages($id);

        try {
            if ($productModel->delete($id)) {
                foreach ($images as $img) {
                    if (!empty($img['image_path'])) {
                        FileUploader::delete($img['image_path']);
                    }
                }
                Session::setFlash('success', 'Product and associated assets deleted successfully.');
            } else {
                Session::setFlash('error', 'Product could not be deleted.');
            }
        } catch (Throwable $e) {
            Session::setFlash('error', 'Cannot delete this product because it has active order records.');
        }
    } else {
        Session::setFlash('error', 'Invalid product identifier.');
    }
}

header('Location: ' . BASE_URL . 'admin/products/index.php');
exit;