<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$userId = (int)($_GET['id'] ?? 0);
if ($userId <= 0) {
    redirect('/admin/user_list.php');
}

// Prevent admin from deleting themselves
if ($userId === (int)$_SESSION['user_id']) {
    flash_set('error', 'Anda tidak bisa menghapus akun sendiri!');
    redirect('/admin/user_list.php');
}

// Fetch user data
$user_result = mysqli_query($conn, "SELECT id, fullname, email FROM users WHERE id=$userId");
$user = mysqli_fetch_assoc($user_result);
if (!$user) {
    redirect('/admin/user_list.php');
}

$title = "Hapus User";
ob_start();
?>

<section class="delete-confirm-section">
  <div class="delete-confirm-wrapper">
    
    <!-- Back Button -->
    <a href="<?= base_path('/admin/user_list.php') ?>" class="back-link">
      <span>← Kembali ke Daftar User</span>
    </a>

    <!-- Confirmation Card -->
    <div class="delete-confirm-card">
      <div class="delete-icon">🗑️</div>
      <h1>Hapus User?</h1>
      <p class="confirm-message">Anda yakin ingin menghapus user berikut:</p>

      <div class="user-info-display">
        <div class="info-item">
          <span class="label">Nama:</span>
          <span class="value"><?= e($user['fullname']) ?></span>
        </div>
        <div class="info-item">
          <span class="label">Email:</span>
          <span class="value"><?= e($user['email']) ?></span>
        </div>
        <div class="info-item">
          <span class="label">User ID:</span>
          <span class="value">#<?= (int)$user['id'] ?></span>
        </div>
      </div>

      <div class="warning-box">
        <span class="warning-icon">⚠️</span>
        <span class="warning-text">
          Semua data user termasuk reservasi akan dihapus dan <strong>tidak bisa dikembalikan</strong>!
        </span>
      </div>

      <div class="confirm-actions">
        <form method="POST" action="<?= base_path('/actions/user_delete.php') ?>" style="display: inline;">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$userId ?>">
          <button type="submit" class="btn btn-danger btn-large">
            <span>🗑️</span> Ya, Hapus User
          </button>
        </form>
        <a href="<?= base_path('/admin/user_list.php') ?>" class="btn btn-secondary btn-large">
          <span>❌</span> Batal
        </a>
      </div>
    </div>

  </div>
</section>

<?php 
$page_content = ob_get_clean();
require __DIR__ . '/../templates/layout-admin.php';
?>
