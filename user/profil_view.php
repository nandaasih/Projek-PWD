<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$userId = (int)$_SESSION['user_id'];

// Fetch user data
$user_result = mysqli_query($conn, "SELECT id, fullname, email, role, created_at FROM users WHERE id=$userId");
$user = mysqli_fetch_assoc($user_result);

// Check profile picture
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'profile_picture'");
if (mysqli_num_rows($check_column) > 0) {
  $profile_result = mysqli_query($conn, "SELECT profile_picture FROM users WHERE id=$userId");
  $profile_data = mysqli_fetch_assoc($profile_result);
  $user['profile_picture'] = $profile_data['profile_picture'] ?? null;
} else {
  $user['profile_picture'] = null;
}

$title = "Profil Saya";
require __DIR__ . '/../templates/header.php';

$profile_pic_url = $user['profile_picture'] ? base_path('/' . $user['profile_picture']) : '';
$initials = strtoupper(substr($user['fullname'] ?? 'U', 0, 1));
// flash messages
$flash_success = flash_get('success');
$flash_error = flash_get('error');
?>

<section class="profile-section">
  <div class="profile-wrapper">
    <?php if ($flash_success): ?>
      <div class="alert alert-success"><?= e($flash_success) ?></div>
    <?php endif; ?>
    <?php if ($flash_error): ?>
      <div class="alert alert-error"><?= e($flash_error) ?></div>
    <?php endif; ?>
    
    <!-- Profile Header -->
    <div class="profile-view-header">
      <div class="profile-view-avatar">
        <?php if (!empty($profile_pic_url)): ?>
          <img src="<?= $profile_pic_url ?>" alt="<?= e($user['fullname']) ?>" class="avatar-image">
        <?php else: ?>
          <div class="avatar-initial"><?= $initials ?></div>
        <?php endif; ?>
      </div>

      <div class="profile-view-info">
        <h1 class="profile-view-name"><?= e($user['fullname'] ?? 'Pengguna') ?></h1>
        <p class="profile-view-role">Pengguna Regular</p>
        <p class="profile-view-date">
          Terdaftar sejak <strong><?= date('d M Y', strtotime($user['created_at'] ?? now())) ?></strong>
        </p>
      </div>

      <div class="profile-view-actions">
        <a href="<?= base_path('/user/profil_edit.php') ?>" class="btn btn-primary">Edit Profil</a>
      </div>
    </div>

    <!-- Account Info -->
    <div class="profile-card">
      <h2 class="card-title">Informasi Akun</h2>
      <div class="info-grid">
        <div class="info-row">
          <span class="info-label">Email</span>
          <span class="info-value"><?= e($user['email'] ?? '-') ?></span>
        </div>
        <div class="info-row">
          <span class="info-label">ID Pengguna</span>
          <span class="info-value">#<?= (int)$user['id'] ?></span>
        </div>
        <div class="info-row">
          <span class="info-label">Role</span>
          <span class="info-value">
            <span class="role-badge">User Regular</span>
          </span>
        </div>
        <div class="info-row">
          <span class="info-label">Status</span>
          <span class="info-value">
            <span class="status-badge-active">✓ Aktif</span>
          </span>
        </div>
        <div class="info-row">
          <span class="info-label">Terdaftar Pada</span>
          <span class="info-value"><?= date('d M Y H:i', strtotime($user['created_at'] ?? now())) ?></span>
        </div>
      </div>
    </div>

    <!-- Reservations Stats -->
    <div class="profile-stats">
      <h2 class="card-title">Statistik Reservasi</h2>
      <div class="stats-grid">
        <?php
        $total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reservasi WHERE user_id=$userId"))['count'] ?? 0;
        $approved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reservasi WHERE user_id=$userId AND status='approved'"))['count'] ?? 0;
        $pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reservasi WHERE user_id=$userId AND status='pending'"))['count'] ?? 0;
        $rejected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reservasi WHERE user_id=$userId AND status='rejected'"))['count'] ?? 0;
        ?>
        
        <div class="stat-card">
          <div class="stat-icon">📌</div>
          <div class="stat-content">
            <div class="stat-number"><?= $total ?></div>
            <div class="stat-label">Total Reservasi</div>
          </div>
        </div>

        <div class="stat-card approved">
          <div class="stat-icon">✓</div>
          <div class="stat-content">
            <div class="stat-number"><?= $approved ?></div>
            <div class="stat-label">Disetujui</div>
          </div>
        </div>

        <div class="stat-card pending">
          <div class="stat-icon">⏳</div>
          <div class="stat-content">
            <div class="stat-number"><?= $pending ?></div>
            <div class="stat-label">Menunggu</div>
          </div>
        </div>

        <div class="stat-card rejected">
          <div class="stat-icon">✗</div>
          <div class="stat-content">
            <div class="stat-number"><?= $rejected ?></div>
            <div class="stat-label">Ditolak</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions removed to simplify UI -->

  </div>
</section>

<?php require __DIR__ . '/../templates/footer.php'; ?>
