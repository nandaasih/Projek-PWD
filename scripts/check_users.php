<?php
require_once __DIR__ . '/../includes/database.php';
header('Content-Type: text/plain; charset=utf-8');
if (!$conn) {
    echo "DB connection failed\n";
    exit;
}

echo "Connected to DB: projek_pwd\n\n";

$res = mysqli_query($conn, "SELECT id, fullname, email, role, created_at FROM users ORDER BY id DESC LIMIT 50");
if ($res === false) {
    echo "Query error: " . mysqli_error($conn) . "\n";
    exit;
}

$rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
if (count($rows) === 0) {
    echo "No users found.\n";
    exit;
}

foreach ($rows as $r) {
    echo "ID: " . (int)$r['id'] . " | " . $r['fullname'] . " | " . $r['email'] . " | " . $r['role'] . " | " . $r['created_at'] . "\n";
}
