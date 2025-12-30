<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/login.php');

verify_csrf_token($_POST['csrf_token'] ?? '');

$email = trim($_POST['email'] ?? '');
$pass  = $_POST['password'] ?? '';

$q = mysqli_prepare($conn, "SELECT id, fullname, role, password FROM users WHERE email=? LIMIT 1");
mysqli_stmt_bind_param($q, "s", $email);
mysqli_stmt_execute($q);
$r = mysqli_stmt_get_result($q);
$user = mysqli_fetch_assoc($r);

if ($user && password_verify($pass, $user['password'])) {
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['name']    = $user['fullname'];
    $_SESSION['role']    = $user['role'];

    if ($user['role'] === 'admin') redirect('/admin/index.php');
    redirect('/user/dashboard.php');
}

redirect('/login.php?error=1');
