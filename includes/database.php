<?php
// includes/database.php
// Database connection with fallback and clearer error messages

// Allow overriding via environment variables (useful for local/production)
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: 3306;
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$db   = getenv('DB_NAME') ?: 'projek_pwd';

// Try connect (suppress default warnings to provide friendly message)
$conn = @mysqli_connect($host, $user, $pass, $db, (int)$port);

// If initial attempt failed, try common alternate host (localhost <-> 127.0.0.1)
if (!$conn) {
	$altHost = ($host === '127.0.0.1' || $host === '::1') ? 'localhost' : '127.0.0.1';
	$conn = @mysqli_connect($altHost, $user, $pass, $db, (int)$port);
}

if (!$conn) {
	$err = mysqli_connect_error();
	$hint = "Pastikan MySQL/MariaDB berjalan (contoh: jalankan XAMPP/MariaDB) dan kredensial di includes/database.php benar.";
	die("DB Error: " . htmlspecialchars($err) . " (host={$host}:{$port}). " . $hint);
}

mysqli_set_charset($conn, "utf8mb4");

if (session_status() === PHP_SESSION_NONE) session_start();
