<?php
// includes/database.php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "projek_pwd";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) die("DB Error: " . mysqli_connect_error());

mysqli_set_charset($conn, "utf8mb4");

if (session_status() === PHP_SESSION_NONE) session_start();
