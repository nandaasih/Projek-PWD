<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/helpers.php';

// Get referrer or default to dashboard
$referrer = $_GET['from'] ?? ($_SERVER['HTTP_REFERER'] ?? '/');
// Sanitize referrer to prevent open redirect
if (strpos($referrer, $_SERVER['HTTP_HOST']) === false && strpos($referrer, 'localhost') === false) {
    $referrer = '/';
}

$title = "Konfirmasi Logout";
ob_start();
?>

<div style="display: flex; align-items: center; justify-content: center; min-height: 100vh; background: transparent; padding: 20px;">
  <div style="background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2); padding: 40px; max-width: 400px; text-align: center;">
    <div style="font-size: 48px; margin-bottom: 20px;">🚪</div>
    <h2 style="margin: 0 0 12px 0; color: #1f2937; font-size: 24px;">Apakah Anda Yakin?</h2>
    <p style="margin: 0 0 30px 0; color: #6b7280; font-size: 14px;">Anda akan keluar dari akun <strong><?= e($_SESSION['name'] ?? 'User') ?></strong>. Lanjutkan?</p>
    
    <div style="display: flex; gap: 12px; justify-content: center;">
      <form method="POST" action="<?= base_path('/actions/logout.php') ?>" style="display: inline;">
        <button type="submit" style="padding: 12px 24px; background: #ef4444; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px;">
          ✓ Ya, Keluar
        </button>
      </form>
      
      <a href="<?= htmlspecialchars($referrer) ?>" style="padding: 12px 24px; background: #e5e7eb; color: #374151; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px; text-decoration: none; display: inline-block;">
        ✕ Tidak, Kembali
      </a>
    </div>
  </div>
</div>

<?php
$page_content = ob_get_clean();
require __DIR__ . '/templates/layout-user-admin.php';
?>
