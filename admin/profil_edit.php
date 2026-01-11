<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
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

// Handle form submission (same logic as user profil_edit)
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

$title = "Edit Profil Admin";
ob_start();

$profile_pic_url = $user['profile_picture'] ? base_path('/' . $user['profile_picture']) : '';
$initials = strtoupper(substr($user['fullname'] ?? 'U', 0, 1));
?>

<div style="max-width: 900px; margin: 0 auto;">
    <!-- Messages -->
    <?php if (!empty($message)): ?>
      <div style="padding: 14px 16px; background: #d1fae5; color: #065f46; border-radius: 8px; border: 1px solid #6ee7b7; margin-bottom: 20px;">
        ✓ <?= e($message) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div style="padding: 14px 16px; background: #fee2e2; color: #991b1b; border-radius: 8px; border: 1px solid #fecaca; margin-bottom: 20px;">
        ✗ <?= e($error) ?>
      </div>
    <?php endif; ?>

    <!-- Profile Header (Like profil.php) -->
    <div style="padding: 24px; background: white; border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 24px; display: flex; gap: 24px; align-items: flex-start;">
      <!-- Avatar -->
      <div style="flex: 0 0 auto;">
        <?php if (!empty($profile_pic_url)): ?>
          <img id="preview" src="<?= $profile_pic_url ?>" alt="<?= e($user['fullname']) ?>" style="width: 140px; height: 140px; border-radius: 12px; object-fit: cover; border: 3px solid #e5e7eb;">
        <?php else: ?>
          <div id="preview" style="width: 140px; height: 140px; border-radius: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 56px; font-weight: bold;">
            <?= $initials ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Profile Info -->
      <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
        <h1 style="margin: 0 0 4px 0; font-size: 28px; font-weight: 700; color: #1f2937;">✏️ <?= e($user['fullname'] ?? 'Admin') ?></h1>
        <p style="margin: 0 0 2px 0; color: #6b7280; font-size: 15px;">Administrator</p>
        <p style="margin: 0 0 16px 0; color: #9ca3af; font-size: 13px;">
          Terdaftar sejak <strong><?= date('d M Y', strtotime($user['created_at'] ?? date('Y-m-d H:i:s'))) ?></strong>
        </p>
        <a href="<?= base_path('/admin/profil.php') ?>" style="display: inline-block; padding: 8px 16px; background: #6b7280; color: white; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 13px; width: fit-content;">
          👁️ Lihat Profil
        </a>
      </div>
    </div>

    <form method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 24px;">
      <?= csrf_field() ?>
      
      <!-- Photo Upload Card -->
      <div style="padding: 24px; background: white; border-radius: 8px; border: 1px solid #e5e7eb;">
        <h2 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 700; color: #1f2937;">📷 Ubah Foto Profil</h2>
        <p style="margin: 0 0 20px 0; color: #6b7280; font-size: 13px;">Pilih foto baru untuk profil Anda</p>

        <div>
          <input type="file" id="profile_picture" name="profile_picture" accept="image/*" style="display: none;">
          <button type="button" onclick="document.getElementById('profile_picture').click()" style="padding: 10px 20px; background: #0066cc; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px; margin-bottom: 12px;">
            🖼️ Pilih Foto
          </button>
          <p style="margin: 0; color: #6b7280; font-size: 12px;">JPG, PNG, GIF, WebP (Max 5MB)</p>
        </div>
      </div>

      <!-- Personal Information -->
      <div style="padding: 24px; background: white; border-radius: 8px; border: 1px solid #e5e7eb;">
        <h2 style="margin: 0 0 20px 0; font-size: 16px; font-weight: 700; color: #1f2937;">👤 Informasi Pribadi</h2>

        <div style="display: flex; flex-direction: column; gap: 16px;">
          <div>
            <label style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 6px;">Nama Lengkap <span style="color: #ef4444;">*</span></label>
            <input type="text" name="fullname" value="<?= e($user['fullname'] ?? '') ?>" required minlength="3" style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;" placeholder="Nama lengkap">
          </div>

          <div>
            <label style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 6px;">Email <span style="color: #ef4444;">*</span></label>
            <input type="email" name="email" value="<?= e($user['email'] ?? '') ?>" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;" placeholder="Email">
          </div>
        </div>
      </div>

      <!-- Password Change -->
      <div style="padding: 24px; background: white; border-radius: 8px; border: 1px solid #e5e7eb;">
        <h2 style="margin: 0 0 20px 0; font-size: 16px; font-weight: 700; color: #1f2937;">🔐 Ubah Password</h2>

        <div style="display: flex; flex-direction: column; gap: 16px;">
          <div>
            <label style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 6px;">Password Saat Ini</label>
            <input type="password" name="current_password" style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;" placeholder="Masukkan password saat ini jika ingin mengganti">
          </div>

          <div>
            <label style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 6px;">Password Baru</label>
            <input type="password" name="password" minlength="6" style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;" placeholder="Minimal 6 karakter">
          </div>

          <div>
            <label style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 6px;">Konfirmasi Password</label>
            <input type="password" name="password_confirm" minlength="6" style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;" placeholder="Ulangi password baru">
          </div>
        </div>

        <div style="margin-top: 16px; padding: 12px; background: #fef3c7; border: 1px solid #fcd34d; border-radius: 6px;">
          <p style="margin: 0; font-size: 13px; color: #92400e;">⚠️ Biarkan kosong jika tidak ingin mengubah password</p>
        </div>
      </div>

      <!-- Form Actions -->
      <div style="display: flex; gap: 12px; margin-bottom: 24px;">
        <button type="submit" style="padding: 14px 32px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 15px; flex: 1;">
          💾 Simpan Perubahan
        </button>
        <a href="<?= base_path('/admin/profil.php') ?>" style="padding: 14px 32px; background: #e5e7eb; color: #1f2937; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 15px; flex: 1; text-decoration: none; display: flex; align-items: center; justify-content: center;">
          ❌ Batal
        </a>
      </div>
    </form>

  </div>
</section>

<script>
// File upload preview and drag-drop
const fileInput = document.getElementById('profile_picture');
const uploadLabel = document.querySelector('.upload-label');
const previewDiv = document.querySelector('.pic-preview');

// Drag and drop
uploadLabel.addEventListener('dragover', (e) => {
  e.preventDefault();
  uploadLabel.classList.add('dragging');
});

uploadLabel.addEventListener('dragleave', () => {
  uploadLabel.classList.remove('dragging');
});

uploadLabel.addEventListener('drop', (e) => {
  e.preventDefault();
  uploadLabel.classList.remove('dragging');
  fileInput.files = e.dataTransfer.files;
  previewFile(e.dataTransfer.files[0]);
});

// File input change
fileInput.addEventListener('change', (e) => {
  if (e.target.files.length > 0) {
    previewFile(e.target.files[0]);
  }
});

function previewFile(file) {
  if (file.type.startsWith('image/')) {
    const reader = new FileReader();
    reader.onload = (e) => {
      previewDiv.innerHTML = `<img src="${e.target.result}" alt="Preview" class="preview-img">`;
    };
    reader.readAsDataURL(file);
  }
}

// Form validation
const form = document.querySelector('.edit-form');
const passwordInput = document.getElementById('password');
const confirmInput = document.getElementById('password_confirm');

form.addEventListener('submit', (e) => {
  if (passwordInput.value !== confirmInput.value) {
    e.preventDefault();
    alert('Password dan konfirmasi password tidak sesuai!');
    confirmInput.focus();
  }
});
</script>

<?php 
$page_content = ob_get_clean();
require __DIR__ . '/../templates/layout-admin.php';
?>