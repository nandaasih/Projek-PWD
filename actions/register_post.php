<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/register.php');

$fullname = trim($_POST['fullname'] ?? '');
$email    = trim($_POST['email'] ?? '');
$pass     = $_POST['password'] ?? '';
$confirm  = $_POST['confirm'] ?? '';

if ($pass !== $confirm) redirect('/register.php?error=Password tidak sama');

$hash = password_hash($pass, PASSWORD_BCRYPT);

// Insert user langsung tanpa verifikasi email
$q = mysqli_prepare($conn, "INSERT INTO users(fullname,email,password,role) VALUES(?,?,?,?)");
if (!$q) redirect('/register.php?error=DB error');

$role = 'user';
mysqli_stmt_bind_param($q, "ssss", $fullname, $email, $hash, $role);

if (!mysqli_stmt_execute($q)) {
    redirect('/register.php?error=Email sudah terdaftar');
}

// Redirect ke login
redirect('/login.php?success=1');
