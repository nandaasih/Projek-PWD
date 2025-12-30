<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

// Tentukan halaman dashboard berdasarkan role
$role = $_SESSION['role'] ?? 'user';

if ($role === 'admin') {
    redirect('/admin/index.php');
} else {
    redirect('/user/dashboard.php');
}
