<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/helpers.php';

$title = "Login";
$err = $_GET['error'] ?? '';
$verify_sent = isset($_GET['verify_sent']) && $_GET['verify_sent'] == 1;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> - Reservasi Ruangan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_path('/assets/styles.css') ?>">
  <script defer src="<?= base_path('/assets/script.js') ?>"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-logo">⤴</div>
    <h2>Selamat Datang di Reservasi Ruangan Rapat</h2>
    <p style="color: #6b7280; text-align: center; margin: 12px 0 20px 0; font-size: 14px;">Masuk ke akun Anda untuk mengelola reservasi</p>
    <?php if ($err): ?><div class="error">Login gagal. Cek email/password.</div><?php endif; ?>
    <?php if ($verify_sent): ?><div class="success">Tautan verifikasi telah dikirim ke email Anda. Silakan cek inbox.</div><?php endif; ?>

    <form class="auth-form" method="post" action="<?= base_path('/actions/login_post.php') ?>">
      <div class="input-group">
        <span class="input-icon">📧</span>
        <input class="auth-input" type="email" name="email" placeholder="Email" required>
      </div>

      <div class="input-group">
        <span class="input-icon">🔒</span>
        <input class="auth-input has-right" id="password" type="password" name="password" placeholder="Password" required>
        <button type="button" class="toggle-password" aria-label="Tampilkan kata sandi"><svg viewBox="0 0 24 24"><path d="M12 5c-7 0-11 6-11 7s4 7 11 7 11-6 11-7-4-7-11-7zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10z"/></svg></button>
      </div>

      <button class="auth-btn" type="submit">Login</button>
      <div class="auth-links">
        <div><a href="<?= base_path('/register.php') ?>">Belum punya akun? Daftar</a></div>
      </div>
    </form>
  </div>
</div>
</body>
</html>
