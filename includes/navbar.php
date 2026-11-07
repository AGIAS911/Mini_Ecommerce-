<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold tracking-wide" href="<?= BASE_URL ?>index.php">
            <i class="bi bi-bag-check-fill text-primary me-1"></i> E-Shop
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <form class="d-flex my-2 my-lg-0 mx-auto col-12 col-lg-5" action="<?= BASE_URL ?>index.php" method="GET">
                <div class="input-group">
                    <input class="form-control" type="search" name="search" placeholder="Search products..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button class="btn btn-outline-light" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item me-lg-3">
                    <a class="nav-link position-relative py-2" href="<?= BASE_URL ?>cart.php">
                        <i class="bi bi-cart3 fs-5"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $cartCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>

                <?php if (AuthMiddleware::isAuthenticated()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-5 me-2"></i>
                            <span><?= htmlspecialchars(Session::get('user_name', 'Account')) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <?php if (AuthMiddleware::isAdmin()): ?>
                                <li>
                                    <a class="dropdown-item text-danger fw-semibold" href="<?= BASE_URL ?>admin/index.php">
                                        <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>profile.php">
                                    <i class="bi bi-person me-2"></i>Profile
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?= BASE_URL ?>logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>login.php">Login</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-primary btn-sm px-3" href="<?= BASE_URL ?>register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>