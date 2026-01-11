<?php
/**
 * Check current admin users in database
 */

require_once __DIR__ . '/../includes/database.php';

$stmt = mysqli_prepare($conn, "SELECT id, fullname, email, role FROM users WHERE role = 'admin'");
if ($stmt === false) {
    die('Error: ' . mysqli_error($conn));
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admins = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Users Check</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #f3f4f6; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #1f2937; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f3f4f6; font-weight: 600; color: #374151; }
        .found { color: #059669; font-weight: 600; }
        .not-found { color: #dc2626; }
    </style>
</head>
<body>
    <div class="container">
        <h1>👥 Admin Users dalam Sistem</h1>
        
        <?php if (count($admins) > 0): ?>
            <p class="found">✅ Ditemukan <?= count($admins) ?> admin user(s):</p>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Fullname</th>
                    <th>Email</th>
                    <th>Role</th>
                </tr>
                <?php foreach ($admins as $admin): ?>
                    <tr>
                        <td><?= $admin['id'] ?></td>
                        <td><?= $admin['fullname'] ?></td>
                        <td><?= $admin['email'] ?></td>
                        <td><?= $admin['role'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <p style="margin-top: 20px;">
                <a href="<?= $_SERVER['REQUEST_URI'] ?>" style="color: #0066cc; text-decoration: none;">🔄 Refresh</a> | 
                <a href="delete_admin.php" style="color: #dc2626; text-decoration: none;">🗑️ Hapus Admin</a>
            </p>
        <?php else: ?>
            <p class="not-found">❌ Tidak ada admin user ditemukan dalam sistem</p>
        <?php endif; ?>
    </div>
</body>
</html>
