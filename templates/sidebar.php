<?php
// templates/sidebar.php
// Global sidebar component yang dapat digunakan di admin dan user dashboard
// Gunakan variable $sidebar_type untuk menentukan jenis sidebar ('admin' atau 'user')

$sidebar_type = $sidebar_type ?? 'user';
$current_path = $_SERVER['REQUEST_URI'] ?? '';

// Define navigation items berdasarkan role
if ($sidebar_type === 'admin'):
    $brand_title = '🏢 Admin';
    $brand_color = '#0b63d7';
    $nav_items = [
        ['label' => 'Dashboard', 'icon' => '🏠', 'href' => '/admin/index.php'],
        ['label' => 'Reservasi', 'icon' => '📅', 'href' => '/admin/reservasi_list.php'],
        ['label' => 'Ruangan', 'icon' => '🚪', 'href' => '/admin/ruangan_list.php'],
        ['label' => 'Pengguna', 'icon' => '👥', 'href' => '/admin/user_list.php'],
        ['label' => 'Profil', 'icon' => '👤', 'href' => '/admin/profil.php'],
        ['label' => 'Logout', 'icon' => '🔓', 'href' => '/actions/logout.php'],
    ];
else:
    $brand_title = '🏢 Reservasi';
    $brand_color = '#0b63d7';
    $nav_items = [
        ['label' => 'Dashboard', 'icon' => '🏠', 'href' => '/user/dashboard.php'],
        ['label' => 'Buat Reservasi', 'icon' => '📅', 'href' => '/user/reservasi_add.php'],
        ['label' => 'Ruangan', 'icon' => '🚪', 'href' => '/user/ruangan_list.php'],
        ['label' => 'Riwayat', 'icon' => '📜', 'href' => '/user/reservasi_history.php'],
        ['label' => 'Profil', 'icon' => '👤', 'href' => '/user/profil_view.php'],
        ['label' => 'Logout', 'icon' => '🔓', 'href' => '/actions/logout.php'],
    ];
endif;
?>

<aside class="sidebar">
  <div class="brand">
    <?= $brand_title ?> — <span style="color: <?= $brand_color ?>">Dashboard</span>
  </div>
  <nav class="nav">
    <?php foreach ($nav_items as $item): ?>
      <a href="<?= base_path($item['href']) ?>" 
         class="nav-link <?= strpos($current_path, $item['href']) !== false ? 'active' : '' ?>">
        <span class="icon"><?= $item['icon'] ?></span>
        <span class="label"><?= $item['label'] ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
</aside>
