<?php
// templates/header.php
require_once __DIR__ . '/../includes/helpers.php';
// If user logged in, try to load profile picture
if (!empty($_SESSION['user_id'])) {
  @require_once __DIR__ . '/../includes/database.php';
  $uid = (int)($_SESSION['user_id'] ?? 0);
  $nav_profile_pic_url = '';
  if ($uid) {
    $col = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'profile_picture'");
    if ($col && mysqli_num_rows($col) > 0) {
      $r = mysqli_query($conn, "SELECT profile_picture FROM users WHERE id=$uid LIMIT 1");
      if ($r) {
        $row = mysqli_fetch_assoc($r);
        if (!empty($row['profile_picture'])) {
          $nav_profile_pic_url = base_path('/' . $row['profile_picture']);
        }
      }
    }
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($title) ? e($title) : "Reservasi Ruangan" ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_path('/assets/styles.css') ?>">
  <link rel="stylesheet" href="<?= base_path('/assets/dashboard.css') ?>">
  <link rel="stylesheet" href="<?= base_path('/assets/profile.css') ?>">
  <script defer src="<?= base_path('/assets/script.js') ?>"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <button id="sidebarToggle" class="sidebar-toggle" aria-label="Toggle sidebar">☰</button>
    <a class="navbar-brand" href="<?= base_path('/') ?>">Reservasi Ruangan Rapat</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="<?= base_path('/') ?>">Beranda</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= base_path('/user/ruangan_list.php') ?>">Ruangan</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= base_path('/chat.php') ?>">Chat Admin</a>
        </li>
        <?php if (!empty($_SESSION['user_id'])): ?>
          <?php
            $role = $_SESSION['role'] ?? 'user';
            $uid = (int)($_SESSION['user_id'] ?? 0);
            $notif_count = 0;
            if (!empty($uid) && isset($conn)) {
              $notif_count = notif_unread_count($conn, $uid);
            }
            $notif_link = $role === 'admin' ? base_path('/admin/index.php') : base_path('/user/dashboard.php');
          ?>
          <li class="nav-item">
            <a class="nav-link position-relative" href="<?= $notif_link ?>" title="Notifikasi">
              <span style="font-size:18px;line-height:1">🔔</span>
              <?php if ($notif_count > 0): ?>
                <span class="badge bg-danger" style="position:relative;top:-8px;left:-6px;font-size:0.65rem"><?= (int)$notif_count ?></span>
              <?php endif; ?>
            </a>
          </li>

          <li class="nav-item">
            <a href="<?php echo $role === 'admin' ? base_path('/admin/profil.php') : base_path('/user/profil.php'); ?>" class="nav-link nav-user-link">
              <div class="nav-user">
                <?php if (!empty($nav_profile_pic_url)): ?>
                  <img src="<?= $nav_profile_pic_url ?>" alt="<?= e($_SESSION['name'] ?? 'User') ?>" class="nav-avatar">
                <?php else: ?>
                  <div class="nav-avatar initial"><?= strtoupper(substr($_SESSION['name'] ?? 'U',0,1)) ?></div>
                <?php endif; ?>
                <span class="user-badge"><?= e($_SESSION['name'] ?? 'User') ?></span>
              </div>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_path('/actions/logout.php') ?>" class="nav-link">Logout</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a href="<?= base_path('/login.php') ?>" class="nav-link">Login</a>
          </li>
          <li class="nav-item">
            <a href="<?= base_path('/register.php') ?>" class="nav-link">Daftar</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="main-container">
