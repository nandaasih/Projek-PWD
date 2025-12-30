<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

 $list = mysqli_query($conn, "SELECT id,fullname,email,role,created_at FROM users ORDER BY created_at DESC");
 if ($list === false) {
   error_log('DB error (user_list): ' . mysqli_error($conn));
   $users = [];
 } else {
   $users = mysqli_fetch_all($list, MYSQLI_ASSOC);
 }

$title="Daftar Pengguna";
require __DIR__ . '/../templates/header.php';
?>

<section class="admin-section">
  <div class="section-header">
    <div class="section-title-wrapper">
      <h1 class="page-title">👥 Daftar Pengguna</h1>
      <p class="page-subtitle">Kelola semua pengguna yang terdaftar dalam sistem</p>
    </div>
    <div class="user-stats">
      <div class="stat-badge">
        <span class="stat-icon">👨‍💼</span>
        <span class="stat-text"><?php 
          $ac = mysqli_query($conn, "SELECT COUNT(*) c FROM users WHERE role='admin'");
          $admin_count = $ac ? (int)(mysqli_fetch_assoc($ac)['c'] ?? 0) : 0;
          echo (int)$admin_count . " Admin";
        ?></span>
      </div>
      <div class="stat-badge">
        <span class="stat-icon">👤</span>
        <span class="stat-text"><?php 
          $uc = mysqli_query($conn, "SELECT COUNT(*) c FROM users WHERE role='user'");
          $user_count = $uc ? (int)(mysqli_fetch_assoc($uc)['c'] ?? 0) : 0;
          echo (int)$user_count . " Pengguna";
        ?></span>
      </div>
    </div>
  </div>

  <?php if (count($users) > 0): ?>
    <div class="users-grid">
      <?php foreach ($users as $user): ?>
        <div class="user-card">
          <div class="user-header">
            <div class="user-avatar">
              <?= strtoupper(substr($user['fullname'], 0, 1)) ?>
            </div>
            <div class="user-name-section">
              <h3 class="user-name"><?= e($user['fullname']) ?></h3>
              <span class="user-role-badge <?= $user['role'] === 'admin' ? 'admin-badge' : 'user-badge' ?>">
                <?= $user['role'] === 'admin' ? '👨‍💼 Admin' : '👤 Pengguna' ?>
              </span>
            </div>
          </div>

          <div class="user-body">
            <div class="user-info-item">
              <span class="info-label">📧 Email</span>
              <span class="info-value email-text"><?= e($user['email']) ?></span>
            </div>
            <div class="user-info-item">
              <span class="info-label">📅 Terdaftar</span>
              <span class="info-value">
                <?= date('d M Y, H:i', strtotime($user['created_at'])) ?>
              </span>
            </div>
            <div class="user-info-item">
              <span class="info-label">🆔 User ID</span>
              <span class="info-value">#<?= (int)$user['id'] ?></span>
            </div>
          </div>

          <div class="user-footer">
            <div class="user-role-display">
              <?php if ($user['role'] === 'admin'): ?>
                <span class="role-badge admin">👨‍💼 Administrator</span>
              <?php else: ?>
                <span class="role-badge user">👤 User Regular</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty-state">
      <div class="empty-icon">👥</div>
      <h3>Belum Ada Pengguna</h3>
      <p>Tidak ada pengguna yang terdaftar dalam sistem.</p>
    </div>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/../templates/footer.php'; ?>
