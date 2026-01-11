<?php
// templates/admin-header.php
// Admin navbar header with navigation menu

if (!function_exists('base_path')) {
    @require_once __DIR__ . '/../includes/helpers.php';
}

// Load profile picture jika ada
$admin_profile_pic_url = '';
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
          $admin_profile_pic_url = base_path('/' . $row['profile_picture']);
        }
      }
    }
  }
}

// Deteksi halaman aktif untuk menu
$current_path = parse_url($_SERVER['REQUEST_URI'] ?? '/admin/index.php', PHP_URL_PATH);
$current_page = basename($current_path ?: 'index.php');
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= base_path('/admin/index.php') ?>" style="font-weight: bold; font-size: 18px;">
      <span style="margin-right:8px;">⚙️</span>Admin Panel
    </a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="adminNavbar">
      <ul class="navbar-nav mx-auto">
        <li class="nav-item">
          <a class="nav-link <?= $current_page === 'index.php' ? 'active' : '' ?>" href="<?= base_path('/admin/index.php') ?>">
            📊 Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $current_page === 'reservasi_list.php' ? 'active' : '' ?>" href="<?= base_path('/admin/reservasi_list.php') ?>">
            📋 Reservasi
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $current_page === 'ruangan_list.php' ? 'active' : '' ?>" href="<?= base_path('/admin/ruangan_list.php') ?>">
            🏛️ Ruangan
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $current_page === 'user_list.php' ? 'active' : '' ?>" href="<?= base_path('/admin/user_list.php') ?>">
            👥 Pengguna
          </a>
        </li>
      </ul>

      <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="adminProfileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?php if (!empty($admin_profile_pic_url)): ?>
              <img src="<?= $admin_profile_pic_url ?>" alt="<?= e($_SESSION['name'] ?? 'Admin') ?>" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;margin-right:8px;">
            <?php else: ?>
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;background:#999;color:white;margin-right:8px;font-weight:bold;">
                <?= strtoupper(substr($_SESSION['name'] ?? 'A',0,1)) ?>
              </div>
            <?php endif; ?>
            <span><?= e($_SESSION['name'] ?? 'Admin') ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminProfileDropdown">
            <li>
              <a class="dropdown-item" href="<?= base_path('/admin/profil.php') ?>">👤 Profil</a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= base_path('/admin/profil_edit.php') ?>">✏️ Edit Profil</a>
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
