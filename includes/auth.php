<?php
// includes/auth.php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';

function require_login(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (!isset($_SESSION['user_id'])) redirect('/login.php');
}

function require_admin(): void {
    require_login();
    if (($_SESSION['role'] ?? '') !== 'admin') redirect('/user/dashboard.php');
}
