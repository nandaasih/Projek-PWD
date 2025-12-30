<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

verify_csrf_token($_POST['csrf_token'] ?? $_GET['csrf'] ?? '');

$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
mysqli_query($conn, "DELETE FROM ruangan WHERE id=$id");

redirect('/admin/ruangan_list.php');
