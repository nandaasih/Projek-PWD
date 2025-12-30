<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

verify_csrf_token($_POST['csrf_token'] ?? $_GET['csrf'] ?? '');

$userId = (int)$_SESSION['user_id'];
$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

mysqli_query($conn, "UPDATE reservasi SET status='canceled' WHERE id=$id AND user_id=$userId AND status='pending'");
redirect('/user/reservasi_history.php');
