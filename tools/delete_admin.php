<?php
/**
 * Script untuk menghapus user admin dengan email admin@projek.local
 * DIRECT DELETE - No password confirmation
 */

require_once __DIR__ . '/../includes/database.php';

// Delete user dengan email admin@projek.local atau dengan role admin
$stmt = mysqli_prepare($conn, "DELETE FROM users WHERE email = ? OR (email = ? AND role = 'admin')");
if ($stmt === false) {
    die('Error prepare: ' . mysqli_error($conn));
}

$email1 = 'admin@projek.local';
$email2 = 'admin';

mysqli_stmt_bind_param($stmt, "ss", $email1, $email2);
$result = mysqli_stmt_execute($stmt);
$affected = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Delete Admin Result</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #f3f4f6; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .success { padding: 20px; background: #d1fae5; color: #065f46; border-radius: 8px; font-weight: 600; border: 1px solid #6ee7b7; margin-bottom: 20px; }
        .error { padding: 20px; background: #fee2e2; color: #991b1b; border-radius: 8px; font-weight: 600; border: 1px solid #fca5a5; margin-bottom: 20px; }
        .info { padding: 20px; background: #e0e7ff; color: #3730a3; border-radius: 8px; font-weight: 600; border: 1px solid #a5b4fc; margin-bottom: 20px; }
        a { color: #0066cc; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗑️ Delete Admin Result</h1>
        
        <?php if ($result): ?>
            <?php if ($affected > 0): ?>
                <div class="success">
                    ✅ Admin user berhasil dihapus!<br>
                    Jumlah user yang dihapus: <strong><?= $affected ?></strong>
                </div>
            <?php else: ?>
                <div class="info">
                    ⚠️ Tidak ada user yang cocok dengan criteria dihapus<br>
                    Kemungkinan user sudah tidak ada atau tidak ditemukan
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="error">
                ❌ Error: Gagal menjalankan query<br>
                <?= htmlspecialchars(mysqli_error($conn)) ?>
            </div>
        <?php endif; ?>
        
        <p style="margin-top: 30px;">
            <a href="check_admin.php">👥 Lihat Daftar Admin</a> | 
            <a href="../admin/user_list.php">👨 Kelola Users</a>
        </p>
    </div>
</body>
</html>
