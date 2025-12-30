<?php
// Resend verification removed - redirect back to profile
require_once __DIR__ . '/../includes/helpers.php';
if (session_status() === PHP_SESSION_NONE) session_start();
flash_set('error', 'Fitur verifikasi email telah dihapus.');
// Redirect depending on role
if (($_SESSION['role'] ?? '') === 'admin') {
    redirect('/admin/profil.php');
}
redirect('/user/profil_view.php');
