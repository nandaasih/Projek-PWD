<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$userId = (int)$_SESSION['user_id'];
$message = '';
$error = '';

// Create uploads directory
$uploads_dir = __DIR__ . '/../uploads/profiles';
if (!is_dir($uploads_dir)) {
    mkdir($uploads_dir, 0755, true);
}

// Fetch user data
$user_result = mysqli_query($conn, "SELECT id, fullname, email, role, created_at, password FROM users WHERE id=$userId");
$user = mysqli_fetch_assoc($user_result);

// Check profile picture column
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'profile_picture'");
if (mysqli_num_rows($check_column) > 0) {
  $profile_result = mysqli_query($conn, "SELECT profile_picture FROM users WHERE id=$userId");
  $profile_data = mysqli_fetch_assoc($profile_result);
  $user['profile_picture'] = $profile_data['profile_picture'] ?? null;
} else {
  $alter_sql = "ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) NULL";
  @mysqli_query($conn, $alter_sql);
  $user['profile_picture'] = null;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // CSRF protection
  if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    $error = 'Token keamanan tidak valid. Coba muat ulang halaman.';
  }
    
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
  $current_password = trim($_POST['current_password'] ?? '');
    $password_confirm = trim($_POST['password_confirm'] ?? '');
    $profile_picture = $user['profile_picture'] ?? '';

    // Validation
    if (empty($fullname)) {
        $error = 'Nama lengkap tidak boleh kosong';
    } elseif (empty($email)) {
        $error = 'Email tidak boleh kosong';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid';
    } elseif (strlen($fullname) < 3) {
        $error = 'Nama lengkap minimal 3 karakter';
    } elseif (!empty($password)) {
        if (strlen($password) < 6) {
            $error = 'Password minimal 6 karakter';
        } elseif ($password !== $password_confirm) {
            $error = 'Password dan konfirmasi password tidak sesuai';
        }
    }

    // if changing password, require current password and verify
    if (empty($error) && !empty($password)) {
      if (empty($current_password)) {
        $error = 'Masukkan password saat ini untuk mengganti password.';
      } else {
        if (!password_verify($current_password, $user['password'])) {
          $error = 'Password saat ini tidak cocok.';
        }
      }
    }

    // email uniqueness check
    if (empty($error)) {
      $email_q = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
      mysqli_stmt_bind_param($email_q, "si", $email, $userId);
      mysqli_stmt_execute($email_q);
      $email_res = mysqli_stmt_get_result($email_q);
      if ($email_res && mysqli_num_rows($email_res) > 0) {
        $error = 'Email sudah digunakan oleh akun lain.';
      }
    }

    // Handle image upload
    if (empty($error) && isset($_FILES['profile_picture']) && $_FILES['profile_picture']['size'] > 0) {
        $file = $_FILES['profile_picture'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024;

        if (!in_array($file['type'], $allowed_types)) {
            $error = 'Format file hanya boleh JPG, PNG, GIF, atau WebP';
        } elseif ($file['size'] > $max_size) {
            $error = 'Ukuran file maksimal 5MB';
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Gagal upload file: ' . $file['error'];
        } else {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $userId . '_' . time() . '.' . $ext;
            $filepath = $uploads_dir . '/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                if (!empty($user['profile_picture']) && file_exists(__DIR__ . '/../' . $user['profile_picture'])) {
                    unlink(__DIR__ . '/../' . $user['profile_picture']);
                }
                $profile_picture = 'uploads/profiles/' . $filename;
            } else {
                $error = 'Gagal menyimpan file gambar';
            }
        }
    }

    if (empty($error)) {
        if (empty($password)) {
            $check_col = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'profile_picture'");
            if (mysqli_num_rows($check_col) === 0) {
              @mysqli_query($conn, "ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) NULL");
              $check_col = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'profile_picture'");
            }
            if (mysqli_num_rows($check_col) > 0) {
              $stmt = mysqli_prepare($conn, "UPDATE users SET fullname=?, email=?, profile_picture=? WHERE id=?");
              mysqli_stmt_bind_param($stmt, "sssi", $fullname, $email, $profile_picture, $userId);
            } else {
              $stmt = mysqli_prepare($conn, "UPDATE users SET fullname=?, email=? WHERE id=?");
              mysqli_stmt_bind_param($stmt, "ssi", $fullname, $email, $userId);
            }
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $check_col = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'profile_picture'");
            if (mysqli_num_rows($check_col) === 0) {
              @mysqli_query($conn, "ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) NULL");
              $check_col = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'profile_picture'");
            }
            if (mysqli_num_rows($check_col) > 0) {
              $stmt = mysqli_prepare($conn, "UPDATE users SET fullname=?, email=?, password=?, profile_picture=? WHERE id=?");
              mysqli_stmt_bind_param($stmt, "ssssi", $fullname, $email, $hashed_password, $profile_picture, $userId);
            } else {
              $stmt = mysqli_prepare($conn, "UPDATE users SET fullname=?, email=?, password=? WHERE id=?");
              mysqli_stmt_bind_param($stmt, "sssi", $fullname, $email, $hashed_password, $userId);
            }
        }

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['name'] = $fullname;
            $message = '✅ Profil berhasil diperbarui';
            $user['fullname'] = $fullname;
            $user['email'] = $email;
            $user['profile_picture'] = $profile_picture;
        } else {
            $error = 'Gagal memperbarui profil: ' . mysqli_error($conn);
        }
    }
}

$title = "Edit Profil";

// Start output buffering
ob_start();

$profile_pic_url = $user['profile_picture'] ? base_path('/' . $user['profile_picture']) : '';
$initials = strtoupper(substr($user['fullname'] ?? 'U', 0, 1));
?>

<div class="profile-edit-wrapper">
    <!-- Page Title -->
    <div style="margin-bottom: 24px;">
      <h2 class="section-title">✏️ Edit Profil</h2>
      <p style="color: #6b7280; margin: 8px 0 0 0;">Perbarui informasi akun dan foto profil Anda</p>
    </div>

    <!-- Messages -->
    <?php if (!empty($message)): ?>
      <div style="padding: 12px 16px; background: #d1fae5; color: #065f46; border-radius: 8px; border: 1px solid #a7f3d0; margin-bottom: 16px;">
        ✓ <?= $message ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div style="padding: 12px 16px; background: #fee2e2; color: #991b1b; border-radius: 8px; border: 1px solid #fecaca; margin-bottom: 16px;">
        ✗ <?= $error ?>
      </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <?= csrf_field() ?>
      
      <!-- Profile Picture Card -->
      <div style="padding: 24px; background: white; border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 24px;">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 8px 0; color: #1f2937;">📷 Foto Profil</h3>
        <p style="color: #6b7280; font-size: 13px; margin: 0 0 16px 0;">Ubah atau hapus foto profil Anda</p>

        <div style="display: grid; grid-template-columns: auto 1fr; gap: 20px; align-items: start;">
          <div style="width: 120px; height: 120px; background: #f3f4f6; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
            <?php if (!empty($profile_pic_url)): ?>
              <img src="<?= $profile_pic_url ?>" alt="<?= e($user['fullname']) ?>" style="width: 100%; height: 100%; object-fit: cover;" id="preview">
            <?php else: ?>
              <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 48px; font-weight: bold;" id="preview">
                <?= $initials ?>
              </div>
            <?php endif; ?>
          </div>

          <div style="flex: 1;">
            <input type="file" id="profile_picture" name="profile_picture" style="display: none;" accept="image/*">
            <button type="button" style="padding: 10px 20px; background: #0066cc; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; margin-bottom: 8px;" onclick="document.getElementById('profile_picture').click();">
              📁 Pilih Foto
            </button>
            <p style="color: #6b7280; font-size: 12px; margin: 8px 0 0 0;">JPG, PNG, GIF, WebP (Max 5MB)</p>
          </div>
        </div>
      </div>

      <!-- Personal Information -->
      <div style="padding: 24px; background: white; border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 24px;">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 16px 0; color: #1f2937;">👤 Informasi Pribadi</h3>

        <div style="margin-bottom: 16px;">
          <label style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 6px;">Nama Lengkap <span style="color: #dc2626;">*</span></label>
          <input type="text" name="fullname" value="<?= e($user['fullname'] ?? '') ?>" required minlength="3" style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; box-sizing: border-box;">
          <p style="color: #6b7280; font-size: 12px; margin: 4px 0 0 0;">Gunakan nama lengkap yang sesungguhnya</p>
        </div>

        <div style="margin-bottom: 16px;">
          <label style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 6px;">Email <span style="color: #dc2626;">*</span></label>
          <input type="email" name="email" value="<?= e($user['email'] ?? '') ?>" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; box-sizing: border-box;">
          <p style="color: #6b7280; font-size: 12px; margin: 4px 0 0 0;">Email digunakan untuk login dan notifikasi penting</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; padding-top: 16px; border-top: 1px solid #e5e7eb;">
          <div style="padding: 12px; background: #f9fafb; border-radius: 6px;">
            <span style="display: block; color: #9ca3af; font-size: 12px; font-weight: 500; margin-bottom: 4px;">🆔 User ID</span>
            <span style="display: block; color: #1f2937; font-weight: 600;">#<?= (int)$user['id'] ?></span>
          </div>
          <div style="padding: 12px; background: #f9fafb; border-radius: 6px;">
            <span style="display: block; color: #9ca3af; font-size: 12px; font-weight: 500; margin-bottom: 4px;">👨‍💼 Role</span>
            <span style="display: block; color: #1f2937; font-weight: 600;">User Regular</span>
          </div>
          <div style="padding: 12px; background: #f9fafb; border-radius: 6px;">
            <span style="display: block; color: #9ca3af; font-size: 12px; font-weight: 500; margin-bottom: 4px;">📅 Terdaftar</span>
            <span style="display: block; color: #1f2937; font-weight: 600;"><?= date('d M Y', strtotime($user['created_at'] ?? now())) ?></span>
          </div>
        </div>
      </div>

      <!-- Password Change -->
      <div style="padding: 24px; background: white; border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 24px;">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 8px 0; color: #1f2937;">🔐 Ubah Password</h3>
        <p style="color: #6b7280; font-size: 13px; margin: 0 0 16px 0;">Biarkan kosong jika tidak ingin mengubah</p>
        
        <div style="margin-bottom: 16px;">
          <label style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 6px;">Password Saat Ini</label>
          <input type="password" name="current_password" placeholder="Masukkan password saat ini jika ingin mengganti" style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; box-sizing: border-box;">
          <p style="color: #6b7280; font-size: 12px; margin: 4px 0 0 0;">Diperlukan saat ingin mengganti password</p>
        </div>

        <div style="margin-bottom: 16px;">
          <label style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 6px;">Password Baru</label>
          <input type="password" name="password" id="password" minlength="6" placeholder="Minimal 6 karakter" style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; box-sizing: border-box;">
          <p style="color: #6b7280; font-size: 12px; margin: 4px 0 0 0;">Gunakan kombinasi huruf besar, kecil, dan angka</p>
        </div>

        <div style="margin-bottom: 0;">
          <label style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 6px;">Konfirmasi Password</label>
          <input type="password" name="password_confirm" id="password_confirm" minlength="6" placeholder="Ulangi password baru" style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; box-sizing: border-box;">
          <p style="color: #6b7280; font-size: 12px; margin: 4px 0 0 0;">Pastikan kedua password sama</p>
        </div>
      </div>

      <!-- Form Actions -->
      <div style="display: flex; gap: 12px;">
        <button type="submit" style="padding: 12px 24px; background: #0066cc; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px;">
          💾 Simpan Perubahan
        </button>
        <a href="<?= base_path('/user/profil_view.php') ?>" style="padding: 12px 24px; background: #e5e7eb; color: #374151; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px; text-decoration: none; display: inline-block;">
          ❌ Batal
        </a>
      </div>
    </form>
</div>

<script>
// File upload preview
const fileInput = document.getElementById('profile_picture');
const preview = document.getElementById('preview');

fileInput.addEventListener('change', (e) => {
  if (e.target.files.length > 0) {
    const file = e.target.files[0];
    if (file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = (event) => {
        preview.innerHTML = '<img src="' + event.target.result + '" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">';
      };
      reader.readAsDataURL(file);
    }
  }
});

// Form validation
const form = document.querySelector('form');
const passwordInput = document.getElementById('password');
const confirmInput = document.getElementById('password_confirm');

form.addEventListener('submit', (e) => {
  if (passwordInput.value && passwordInput.value !== confirmInput.value) {
    e.preventDefault();
    alert('Password dan konfirmasi password tidak sesuai!');
    confirmInput.focus();
  }
});
</script>

<?php
// Capture the HTML content
$page_content = ob_get_clean();

// Load the user layout template with admin style
require __DIR__ . '/../templates/layout-user-admin.php';
