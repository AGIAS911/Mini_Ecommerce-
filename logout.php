<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/helpers/Session.php';

Session::destroy();

Session::start();
Session::setFlash('success', 'You have been logged out successfully.');

header('Location: ' . BASE_URL . 'login.php');
exit;