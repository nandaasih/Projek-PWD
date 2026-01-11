<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$userId = (int)$_SESSION['user_id'];

// Fetch user data
$user_result = mysqli_query($conn, "SELECT id, fullname, email, role, created_at FROM users WHERE id=$userId");
$user = mysqli_fetch_assoc($user_result);

// Check profile picture
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'profile_picture'");
if (mysqli_num_rows($check_column) > 0) {
  $profile_result = mysqli_query($conn, "SELECT profile_picture FROM users WHERE id=$userId");
  $profile_data = mysqli_fetch_assoc($profile_result);
  $user['profile_picture'] = $profile_data['profile_picture'] ?? null;
} else {
  $user['profile_picture'] = null;
}

$title = "Profil Saya";

// Start output buffering
ob_start();

$profile_pic_url = $user['profile_picture'] ? base_path('/' . $user['profile_picture']) : '';
$initials = strtoupper(substr($user['fullname'] ?? 'U', 0, 1));
// flash messages
$flash_success = flash_get('success');
$flash_error = flash_get('error');
?>

<div class="profile-wrapper">
    <?php if ($flash_success): ?>
      <div style="padding: 12px 16px; background: #d1fae5; color: #065f46; border-radius: 8px; border: 1px solid #a7f3d0; margin-bottom: 16px;">
        ✓ <?= e($flash_success) ?>
      </div>
    <?php endif; ?>
    <?php if ($flash_error): ?>
      <div style="padding: 12px 16px; background: #fee2e2; color: #991b1b; border-radius: 8px; border: 1px solid #fecaca; margin-bottom: 16px;">
        ✗ <?= e($flash_error) ?>
      </div>
    <?php endif; ?>
    
    <!-- Profile Header -->
    <div style="display: flex; gap: 24px; align-items: flex-start; margin-bottom: 24px; padding: 24px; background: white; border-radius: 8px; border: 1px solid #e5e7eb;">
      <div style="flex-shrink: 0;">
        <?php if (!empty($profile_pic_url)): ?>
          <img src="<?= $profile_pic_url ?>" alt="<?= e($user['fullname']) ?>" style="width: 120px; height: 120px; border-radius: 12px; object-fit: cover;">
        <?php else: ?>
          <div style="width: 120px; height: 120px; border-radius: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 48px; font-weight: bold;">
            <?= $initials ?>
          </div>
        <?php endif; ?>
      </div>

      <div style="flex: 1;">
        <h1 style="font-size: 28px; font-weight: 700; margin: 0 0 8px 0; color: #1f2937;">
          👤 <?= e($user['fullname'] ?? 'Pengguna') ?>
        </h1>
        <p style="color: #6b7280; font-size: 14px; margin: 0 0 12px 0;">Pengguna Regular</p>
        <p style="color: #6b7280; font-size: 13px; margin: 0;">
          Terdaftar sejak <strong><?= date('d M Y', strtotime($user['created_at'] ?? now())) ?></strong>
        </p>
      </div>

      <div>
        <a href="<?= base_path('/user/profil_edit.php') ?>" class="btn btn-primary" style="padding: 10px 20px; background: #0066cc; color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">✏️ Edit Profil</a>
      </div>
    </div>

    <!-- Account Info -->
    <div style="padding: 24px; background: white; border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 24px;">
      <h2 style="font-size: 18px; font-weight: 600; margin: 0 0 16px 0; color: #1f2937;">📋 Informasi Akun</h2>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
        <div style="padding: 12px; background: #f9fafb; border-radius: 6px;">
          <span style="display: block; color: #9ca3af; font-size: 12px; font-weight: 500; margin-bottom: 4px;">Email</span>
          <span style="display: block; color: #1f2937; font-weight: 600;"><?= e($user['email'] ?? '-') ?></span>
        </div>
        <div style="padding: 12px; background: #f9fafb; border-radius: 6px;">
          <span style="display: block; color: #9ca3af; font-size: 12px; font-weight: 500; margin-bottom: 4px;">ID Pengguna</span>
          <span style="display: block; color: #1f2937; font-weight: 600;">#<?= (int)$user['id'] ?></span>
        </div>
        <div style="padding: 12px; background: #f9fafb; border-radius: 6px;">
          <span style="display: block; color: #9ca3af; font-size: 12px; font-weight: 500; margin-bottom: 4px;">Role</span>
          <span style="display: inline-block; background: #e0e7ff; color: #3730a3; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;">User Regular</span>
        </div>
        <div style="padding: 12px; background: #f9fafb; border-radius: 6px;">
          <span style="display: block; color: #9ca3af; font-size: 12px; font-weight: 500; margin-bottom: 4px;">Status</span>
          <span style="display: inline-block; background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;">✓ Aktif</span>
        </div>
        <div style="padding: 12px; background: #f9fafb; border-radius: 6px;">
          <span style="display: block; color: #9ca3af; font-size: 12px; font-weight: 500; margin-bottom: 4px;">Terdaftar Pada</span>
          <span style="display: block; color: #1f2937; font-weight: 600;"><?= date('d M Y H:i', strtotime($user['created_at'] ?? now())) ?></span>
        </div>
      </div>
    </div>

    <!-- Reservations Stats -->
    <div style="padding: 24px; background: white; border-radius: 8px; border: 1px solid #e5e7eb;">
      <h2 style="font-size: 18px; font-weight: 600; margin: 0 0 16px 0; color: #1f2937;">📊 Statistik Reservasi</h2>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;">
        <?php
        $total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reservasi WHERE user_id=$userId"))['count'] ?? 0;
        $approved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reservasi WHERE user_id=$userId AND status='approved'"))['count'] ?? 0;
        $pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reservasi WHERE user_id=$userId AND status='pending'"))['count'] ?? 0;
        $rejected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reservasi WHERE user_id=$userId AND status='rejected'"))['count'] ?? 0;
        ?>
        
        <div style="padding: 16px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; color: white;">
          <div style="font-size: 24px; font-weight: 700; margin-bottom: 4px;"><?= $total ?></div>
          <div style="font-size: 12px; opacity: 0.9;">Total Reservasi</div>
        </div>

        <div style="padding: 16px; background: #d1fae5; border-radius: 8px;">
          <div style="font-size: 24px; font-weight: 700; margin-bottom: 4px; color: #065f46;"><?= $approved ?></div>
          <div style="font-size: 12px; color: #065f46;">Disetujui</div>
        </div>

        <div style="padding: 16px; background: #fef3c7; border-radius: 8px;">
          <div style="font-size: 24px; font-weight: 700; margin-bottom: 4px; color: #92400e;"><?= $pending ?></div>
          <div style="font-size: 12px; color: #92400e;">Menunggu</div>
        </div>

        <div style="padding: 16px; background: #fee2e2; border-radius: 8px;">
          <div style="font-size: 24px; font-weight: 700; margin-bottom: 4px; color: #991b1b;"><?= $rejected ?></div>
          <div style="font-size: 12px; color: #991b1b;">Ditolak</div>
        </div>
      </div>
    </div>
</div>

<?php
// Capture the HTML content
$page_content = ob_get_clean();

// Load the user layout template with admin style
require __DIR__ . '/../templates/layout-user-admin.php';
