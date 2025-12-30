<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/helpers.php';

$title = "Login";
require __DIR__ . '/templates/header.php';
$err = $_GET['error'] ?? '';
$verify_sent = isset($_GET['verify_sent']) && $_GET['verify_sent'] == 1;
?>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-logo">⤴</div>
    <h2>Masuk ke Akun</h2>
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
<?php require __DIR__ . '/templates/footer.php'; ?>
