<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../classes/Cart.php';

$cart = new Cart();
$cartCount = $cart->getCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'E-Shop') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

<?php require_once __DIR__ . '/navbar.php'; ?>

<main class="flex-grow-1 py-4">
    <div class="container">
        <?php if ($success = Session::getFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($error = Session::getFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>