<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/helpers/Session.php';
require_once __DIR__ . '/helpers/AuthMiddleware.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Order.php';

AuthMiddleware::requireAuth();

$userId = (int)Session::get('user_id');
$userModel = new User();
$orderModel = new Order();

$errors = [];
$successMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($name)) {
        $errors['name'] = 'Full name is required.';
    }

    if (empty($email)) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email address format.';
    } elseif ($userModel->emailExists($email, $userId)) {
        $errors['email'] = 'This email is already in use by another account.';
    }

    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors['password'] = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }
    }

    if (empty($errors)) {
        $passwordToUpdate = !empty($password) ? $password : null;
        if ($userModel->updateProfile($userId, $name, $email, $passwordToUpdate)) {
            Session::set('user_name', $name);
            Session::set('user_email', $email);
            Session::setFlash('success', 'Profile updated successfully.');
            header('Location: ' . BASE_URL . 'profile.php');
            exit;
        } else {
            $errors['general'] = 'Failed to update profile. Please try again.';
        }
    }
}

$currentUser = $userModel->findById($userId);
if (!$currentUser) {
    Session::destroy();
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

$userOrders = $orderModel->getByUserId($userId);

$pageTitle = 'My Profile - E-Shop';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h1 class="h3 fw-bold">My Account</h1>
        <p class="text-muted mb-0">Manage your personal information and view your order history.</p>
    </div>
</div>

<?php if (isset($errors['general'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($errors['general']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center p-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-person-fill fs-1"></i>
                </div>
                <h5 class="fw-bold mb-1"><?= htmlspecialchars($currentUser['name']) ?></h5>
                <p class="text-muted small mb-2"><?= htmlspecialchars($currentUser['email']) ?></p>
                <span class="badge bg-secondary text-uppercase"><?= htmlspecialchars($currentUser['role']) ?></span>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-semibold">Edit Details</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>profile.php" method="POST" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold small">Full Name</label>
                        <input type="text" 
                               class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                               id="name" 
                               name="name" 
                               value="<?= htmlspecialchars($_POST['name'] ?? $currentUser['name']) ?>" 
                               required>
                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold small">Email Address</label>
                        <input type="email" 
                               class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                               id="email" 
                               name="email" 
                               value="<?= htmlspecialchars($_POST['email'] ?? $currentUser['email']) ?>" 
                               required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                        <?php endif; ?>
                    </div>

                    <hr class="my-3">

                    <p class="small text-muted mb-2">Leave blank if you do not want to change your password.</p>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold small">New Password</label>
                        <input type="password" 
                               class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                               id="password" 
                               name="password" 
                               placeholder="Min 6 characters">
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label fw-semibold small">Confirm New Password</label>
                        <input type="password" 
                               class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                               id="confirm_password" 
                               name="confirm_password" 
                               placeholder="Repeat new password">
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['confirm_password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-semibold">
                        Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-semibold">Order History</h5>
                <span class="badge bg-secondary"><?= count($userOrders) ?> Orders</span>
            </div>

            <?php if (empty($userOrders)): ?>
                <div class="card-body text-center py-5">
                    <i class="bi bi-bag-x display-4 text-muted"></i>
                    <h5 class="mt-3 fw-semibold">No orders placed yet</h5>
                    <p class="text-muted small">Once you checkout items with cash on delivery, they will appear here.</p>
                    <a href="<?= BASE_URL ?>index.php" class="btn btn-outline-primary btn-sm mt-2">
                        Browse Products
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Order ID</th>
                                <th scope="col">Phone</th>
                                <th scope="col">Total</th>
                                <th scope="col">Payment</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($userOrders as $order): ?>
                                <tr>
                                    <td class="fw-bold">
                                        #ORD-<?= str_pad((string)$order['id'], 6, '0', STR_PAD_LEFT) ?>
                                    </td>
                                    <td class="small text-muted">
                                        <?= htmlspecialchars($order['shipping_phone']) ?>
                                    </td>
                                    <td class="fw-semibold text-primary">
                                        $<?= number_format($order['total_amount'], 2) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $order['payment_status'] === 'paid' ? 'success' : 'secondary' ?> text-capitalize">
                                            <?= htmlspecialchars($order['payment_status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $order['order_status'] === 'delivered' ? 'success' : 'warning text-dark' ?> text-capitalize">
                                            <?= htmlspecialchars($order['order_status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>confirmation.php?order_id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-receipt me-1"></i> Receipt
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>