<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/helpers/Session.php';
require_once __DIR__ . '/helpers/AuthMiddleware.php';
require_once __DIR__ . '/classes/User.php';

AuthMiddleware::requireGuest();

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email)) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    }

    if (empty($errors)) {
        $userModel = new User();
        $user = $userModel->login($email, $password);

        if ($user) {
            Session::set('user_id', (int)$user['id']);
            Session::set('user_name', $user['name']);
            Session::set('user_email', $user['email']);
            Session::set('user_role', $user['role']);

            Session::setFlash('success', 'Welcome back, ' . $user['name'] . '!');

            if ($user['role'] === 'admin') {
                header('Location: ' . BASE_URL . 'admin/index.php');
            } else {
                header('Location: ' . BASE_URL . 'index.php');
            }
            exit;
        } else {
            $errors['auth'] = 'Invalid email address or password.';
        }
    }
}

$pageTitle = 'Login - E-Shop';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle mb-2" style="width: 50px; height: 50px;">
                        <i class="bi bi-person-fill fs-3"></i>
                    </div>
                    <h3 class="fw-bold">Sign In</h3>
                    <p class="text-muted small">Access your account and track your orders</p>
                </div>

                <?php if (isset($errors['auth'])): ?>
                    <div class="alert alert-danger py-2 small" role="alert">
                        <i class="bi bi-exclamation-circle me-1"></i> <?= htmlspecialchars($errors['auth']) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>login.php" method="POST" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold small">Email Address</label>
                        <input type="email" 
                               class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                               id="email" 
                               name="email" 
                               value="<?= htmlspecialchars($email) ?>" 
                               placeholder="name@example.com" 
                               required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold small">Password</label>
                        <input type="password" 
                               class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                               id="password" 
                               name="password" 
                               placeholder="Enter your password" 
                               required>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary py-2 fw-semibold">
                            Sign In
                        </button>
                    </div>
                </form>

                <div class="text-center mt-4 pt-3 border-top">
                    <span class="text-muted small">Don't have an account?</span>
                    <a href="<?= BASE_URL ?>register.php" class="text-decoration-none small fw-semibold ms-1">Create one</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>