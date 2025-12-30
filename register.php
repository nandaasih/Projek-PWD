<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/helpers.php';

$title = "Register";
require __DIR__ . '/templates/header.php';
$err = $_GET['error'] ?? '';
?>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-logo">✦</div>
    <h2>Daftar Akun Baru</h2>
    <?php if ($err): ?><div class="error"><?= e($err) ?></div><?php endif; ?>

    <form class="auth-form" method="post" action="<?= base_path('/actions/register_post.php') ?>">
      <?= csrf_field() ?>
      <div class="input-group">
        <span class="input-icon">👤</span>
        <input class="auth-input" type="text" name="fullname" placeholder="Nama" required>
      </div>

      <div class="input-group">
        <span class="input-icon">📧</span>
        <input class="auth-input" type="email" name="email" placeholder="Email" required>
      </div>

      <div class="input-group">
        <span class="input-icon">🔒</span>
        <input class="auth-input has-right" id="password-reg" type="password" name="password" placeholder="Kata Sandi" required>
        <button type="button" class="toggle-password" data-target="password-reg" aria-label="Tampilkan kata sandi"><svg viewBox="0 0 24 24"><path d="M12 5c-7 0-11 6-11 7s4 7 11 7 11-6 11-7-4-7-11-7zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10z"/></svg></button>
      </div>

      <div class="input-group">
        <span class="input-icon">🔒</span>
        <input class="auth-input has-right" id="confirm-reg" type="password" name="confirm" placeholder="Konfirmasi Kata Sandi" required>
        <button type="button" class="toggle-password" data-target="confirm-reg" aria-label="Tampilkan kata sandi"><svg viewBox="0 0 24 24"><path d="M12 5c-7 0-11 6-11 7s4 7 11 7 11-6 11-7-4-7-11-7zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10z"/></svg></button>
      </div>

      <button class="auth-btn" type="submit">Daftar</button>
      <div class="auth-links">
        <div>Sudah punya akun? <a href="<?= base_path('/login.php') ?>">Login</a></div>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/templates/footer.php'; ?>
