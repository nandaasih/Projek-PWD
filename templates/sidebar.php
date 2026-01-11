<?php
/**
 * templates/sidebar.php
 * Global reusable sidebar component untuk Admin & User Dashboard
 * 
 * Features:
 * - Dynamic active page detection menggunakan $_SERVER['PHP_SELF']
 * - Role-based menu rendering (Admin vs User)
 * - Premium styling dengan white card, rounded corners, soft shadow
 * - Session-based role detection
 */

// Mulai session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Deteksi role dari session (boleh dioverride oleh $sidebar_type)
$user_role = $sidebar_type ?? $_SESSION['role'] ?? 'user';

// Pastikan helper tersedia (untuk base_path, e, dll.)
if (!function_exists('base_path')) {
    @require_once __DIR__ . '/../includes/helpers.php';
}

// Gunakan REQUEST_URI untuk deteksi halaman (lebih stabil ketika include tanpa header)
$current_path = parse_url($_SERVER['REQUEST_URI'] ?? '/index.php', PHP_URL_PATH);
$current_page = basename($current_path ?: 'index.php');

/**
 * Fungsi untuk menentukan apakah halaman sedang aktif
 */
function isPageActive($page_file, $current_page) {
    return $current_page === $page_file ? 'active' : '';
}

/**
 * Definisi menu berdasarkan role
 */
$menu_items = [
    'admin' => [
        [
            'label' => 'Dashboard',
            'icon' => '📊',
            'href' => base_path('/admin/index.php'),
            'page' => 'index.php'
        ],
        [
            'label' => 'Reservasi',
            'icon' => '📋',
            'href' => base_path('/admin/reservasi_list.php'),
            'page' => 'reservasi_list.php'
        ],
        [
            'label' => 'Ruangan',
            'icon' => '🏛️',
            'href' => base_path('/admin/ruangan_list.php'),
            'page' => 'ruangan_list.php'
        ],
        [
            'label' => 'Pengguna',
            'icon' => '👥',
            'href' => base_path('/admin/user_list.php'),
            'page' => 'user_list.php'
        ],
        [
            'label' => 'Profil',
            'icon' => '👤',
            'href' => base_path('/admin/profil.php'),
            'page' => 'profil.php'
        ],
        [
            'label' => 'Logout',
            'icon' => '🚪',
            'href' => base_path('/confirm_logout.php'),
            'page' => 'logout.php'
        ],
    ],
    'user' => [
        [
            'label' => 'Dashboard',
            'icon' => '📊',
            'href' => base_path('/user/dashboard.php'),
            'page' => 'dashboard.php'
        ],
        [
            'label' => 'Buat Reservasi',
            'icon' => '➕',
            'href' => base_path('/user/reservasi_add.php'),
            'page' => 'reservasi_add.php'
        ],
        [
            'label' => 'Cek Ketersediaan',
            'icon' => '📅',
            'href' => base_path('/user/cek_ketersediaan.php'),
            'page' => 'cek_ketersediaan.php'
        ],
        [
            'label' => 'Ruangan',
            'icon' => '🏛️',
            'href' => base_path('/user/ruangan_list.php'),
            'page' => 'ruangan_list.php'
        ],
        [
            'label' => 'Riwayat',
            'icon' => '📜',
            'href' => base_path('/user/reservasi_history.php'),
            'page' => 'reservasi_history.php'
        ],
        [
            'label' => 'Profil',
            'icon' => '👤',
            'href' => base_path('/user/profil_view.php'),
            'page' => 'profil_view.php'
        ],
        [
            'label' => 'Logout',
            'icon' => '🚪',
            'href' => base_path('/confirm_logout.php'),
            'page' => 'logout.php'
        ],
    ]
];

// Tentukan menu berdasarkan role
$items = $menu_items[$user_role] ?? $menu_items['user'];
$brand_icon = $user_role === 'admin' ? '⚙️' : '🏢';
$brand_text = $user_role === 'admin' ? 'Admin Dashboard' : 'Reservasi Ruangan';
?>

<aside class="sidebar">
    <div class="brand">
        <div style="display:flex;align-items:center;gap:10px">
            <div class="brand-icon"><?= $brand_icon ?></div>
            <?php if ($user_role === 'admin'): ?>
                <div class="brand-title"><span class="brand-role">Admin</span> — <a class="brand-current" href="<?= base_path('/admin/index.php') ?>">Dashboard</a></div>
            <?php else: ?>
                <div class="brand-title"><?= htmlspecialchars($brand_text) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <nav class="nav">
        <?php foreach ($items as $item): ?>
            <a href="<?= htmlspecialchars($item['href']) ?>" class="<?= isPageActive($item['page'], $current_page) ?>">
                <span class="icon"><?= $item['icon'] ?></span>
                <span class="label"><?= htmlspecialchars($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="user-block" style="margin-top:16px;">
        <div style="display:flex;gap:12px;align-items:center;padding:12px;border-radius:8px;border:1px solid #eef4ff;background:#fff">
            <div style="width:48px;height:48px;border-radius:8px;overflow:hidden;display:flex;align-items:center;justify-content:center;background:#f7fafc">
                <?php
                $avatar = $_SESSION['profile_picture'] ?? null;
                $avatar_fs = $avatar ? __DIR__ . '/../uploads/profiles/' . $avatar : '';
                if ($avatar && $avatar_fs && file_exists($avatar_fs)):
                    $avatar_url = base_path('/uploads/profiles/' . $avatar);
                ?>
                    <img src="<?= htmlspecialchars($avatar_url) ?>" alt="Profile" style="width:100%;height:100%;object-fit:cover">
                <?php else: ?>
                    <div style="font-weight:700;color:#0f172a"><?= strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)) ?></div>
                <?php endif; ?>
            </div>
            <div style="flex:1">
                <div style="font-weight:700;font-size:14px"><?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></div>
                <div style="font-size:12px;color:#64748b"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></div>
            </div>
        </div>
    </div>
</aside>
