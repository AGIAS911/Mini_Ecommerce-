<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/Session.php';
require_once __DIR__ . '/../../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../../classes/Order.php';

AuthMiddleware::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = isset($_POST['order_id']) && is_numeric($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $orderStatus = trim($_POST['order_status'] ?? '');
    $paymentStatus = trim($_POST['payment_status'] ?? '');

    if ($orderId > 0 && !empty($orderStatus) && !empty($paymentStatus)) {
        $orderModel = new Order();
        if ($orderModel->updateStatus($orderId, $orderStatus, $paymentStatus)) {
            Session::setFlash('success', 'Order status updated successfully.');
        } else {
            Session::setFlash('error', 'Failed to update order status. Invalid parameters provided.');
        }
        header('Location: ' . BASE_URL . 'admin/orders/view.php?id=' . $orderId);
        exit;
    } else {
        Session::setFlash('error', 'Missing required order status parameters.');
    }
}

header('Location: ' . BASE_URL . 'admin/orders/index.php');
exit;