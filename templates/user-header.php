<?php
// templates/user-header.php
// User navbar header with navigation menu (Admin style)

if (!function_exists('base_path')) {
    @require_once __DIR__ . '/../includes/helpers.php';
}

// Load profile picture jika ada
$user_profile_pic_url = '';
if (!empty($_SESSION['user_id'])) {
  @require_once __DIR__ . '/../includes/database.php';
  $uid = (int)($_SESSION['user_id'] ?? 0);
  if ($uid) {
    $col = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'profile_picture'");
    if ($col && mysqli_num_rows($col) > 0) {
      $r = mysqli_query($conn, "SELECT profile_picture FROM users WHERE id=$uid LIMIT 1");
      if ($r) {
        $row = mysqli_fetch_assoc($r);
        if (!empty($row['profile_picture'])) {
          $user_profile_pic_url = base_path('/' . $row['profile_picture']);
        }
      }
    }
  }
}

// Deteksi halaman aktif untuk menu
$current_path = parse_url($_SERVER['REQUEST_URI'] ?? '/user/dashboard.php', PHP_URL_PATH);
$current_page = basename($current_path ?: 'dashboard.php');
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= base_path('/user/dashboard.php') ?>" style="font-weight: bold; font-size: 18px;">
      <span style="margin-right:8px;">📌</span>Dashboard
    </a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#userNavbar" aria-controls="userNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="userNavbar">
      <ul class="navbar-nav mx-auto">
        <li class="nav-item">
          <a class="nav-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>" href="<?= base_path('/user/dashboard.php') ?>">
            📊 Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $current_page === 'reservasi_list.php' ? 'active' : '' ?>" href="<?= base_path('/user/reservasi_list.php') ?>">
            📋 Reservasi Saya
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $current_page === 'ruangan_list.php' ? 'active' : '' ?>" href="<?= base_path('/user/ruangan_list.php') ?>">
            🏛️ Daftar Ruangan
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $current_page === 'reservasi_history.php' ? 'active' : '' ?>" href="<?= base_path('/user/reservasi_history.php') ?>">
            ⏰ Riwayat
          </a>
        </li>
      </ul>

      <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userProfileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?php if (!empty($user_profile_pic_url)): ?>
              <img src="<?= $user_profile_pic_url ?>" alt="<?= e($_SESSION['name'] ?? 'User') ?>" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;margin-right:8px;">
            <?php else: ?>
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;background:#999;color:white;margin-right:8px;font-weight:bold;">
                <?= strtoupper(substr($_SESSION['name'] ?? 'U',0,1)) ?>
              </div>
            <?php endif; ?>
            <span><?= e($_SESSION['name'] ?? 'User') ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userProfileDropdown">
            <li>
              <a class="dropdown-item" href="<?= base_path('/user/profil_view.php') ?>">👤 Profil</a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= base_path('/user/profil_edit.php') ?>">✏️ Edit Profil</a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item text-danger" href="<?= base_path('/confirm_logout.php') ?>">🚪 Logout</a>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
