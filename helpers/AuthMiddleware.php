<?php
require_once __DIR__ . '/Session.php';
require_once __DIR__ . '/../config/config.php';

class AuthMiddleware {
    public static function isAuthenticated(): bool {
        return Session::has('user_id');
    }

    public static function isAdmin(): bool {
        return self::isAuthenticated() && Session::get('user_role') === 'admin';
    }

    public static function requireAuth(): void {
        if (!self::isAuthenticated()) {
            Session::setFlash('error', 'Please log in to continue.');
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }
    }

    public static function requireAdmin(): void {
        self::requireAuth();

        if (!self::isAdmin()) {
            Session::setFlash('error', 'Access denied. Administrator privileges required.');
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }
    }

    public static function requireGuest(): void {
        if (self::isAuthenticated()) {
            $redirectUrl = self::isAdmin() ? BASE_URL . 'admin/index.php' : BASE_URL . 'index.php';
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
}