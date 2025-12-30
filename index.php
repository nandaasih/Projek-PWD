<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/helpers.php';

if (!empty($_SESSION['user_id'])) {
    if (($_SESSION['role'] ?? '') === 'admin') redirect('/admin/index.php');
    redirect('/user/dashboard.php');
}

$title = "Reservasi Ruangan - Solusi Booking Ruang Pertemuan";
require __DIR__ . '/templates/header.php';
?>

<section class="landing-hero">
  <div class="hero-container">
    <div class="hero-content">
      <div class="hero-badge">✨ Solusi Terpercaya</div>
      <h1 class="hero-title">Manajemen Reservasi<br>Ruang Pertemuan yang Mudah</h1>
      <p class="hero-subtitle">Memudahkan tim Anda untuk memesan ruang pertemuan dan meja kerja dengan cepat melalui aplikasi mobile dan web</p>
      
      <div class="hero-buttons">
        <a href="<?= base_path('/register.php') ?>" class="btn-primary">Mulai Sekarang</a>
        <a href="#features" class="btn-secondary">Pelajari Lebih Lanjut →</a>
      </div>

      <div class="hero-stats">
        <div class="hero-stat">
          <div class="stat-number">500+</div>
          <div class="stat-name">Pengguna Aktif</div>
        </div>
        <div class="hero-stat">
          <div class="stat-number">1000+</div>
          <div class="stat-name">Reservasi Sukses</div>
        </div>
        <div class="hero-stat">
          <div class="stat-number">98%</div>
          <div class="stat-name">Kepuasan Pengguna</div>
        </div>
      </div>
    </div>

    <div class="hero-image">
      <div class="image-placeholder">
        <div class="placeholder-content">
          <span class="placeholder-icon">📅</span>
          <span class="placeholder-text">Manajemen Ruangan Modern</span>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="features-landing" id="features">
  <div class="features-container">
    <h2 class="section-heading">Fitur Unggulan</h2>
    
    <div class="features-cards-grid">
      <div class="feature-landing-card">
        <span class="feature-icon">⚡</span>
        <h3>Booking Instan</h3>
        <p>Pesan ruangan dalam hitungan detik tanpa perlu antri</p>
      </div>

      <div class="feature-landing-card">
        <span class="feature-icon">📱</span>
        <h3>Multi-Platform</h3>
        <p>Akses dari smartphone, tablet, atau desktop Anda</p>
      </div>

      <div class="feature-landing-card">
        <span class="feature-icon">🔔</span>
        <h3>Notifikasi Real-Time</h3>
        <p>Dapatkan update status reservasi secara langsung</p>
      </div>

      <div class="feature-landing-card">
        <span class="feature-icon">📊</span>
        <h3>Analytics & Laporan</h3>
        <p>Pantau penggunaan ruangan dengan dashboard analitik</p>
      </div>

      <div class="feature-landing-card">
        <span class="feature-icon">👥</span>
        <h3>Team Management</h3>
        <p>Kelola anggota tim dan hak akses dengan mudah</p>
      </div>

      <div class="feature-landing-card">
        <span class="feature-icon">🔒</span>
        <h3>Keamanan Terjamin</h3>
        <p>Data Anda dilindungi dengan enkripsi tingkat enterprise</p>
      </div>
    </div>
  </div>
</section>

<section class="cta-section">
  <div class="cta-container">
    <h2>Siap Memulai?</h2>
    <p>Daftar sekarang dan kelola reservasi ruangan Anda dengan lebih efisien</p>
    <div class="cta-buttons">
      <a href="<?= base_path('/register.php') ?>" class="btn-primary-large">Daftar Gratis</a>
      <a href="<?= base_path('/login.php') ?>" class="btn-secondary-large">Sudah Punya Akun? Login</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/templates/footer.php'; ?>
