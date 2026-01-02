<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$userId = (int)($_GET['id'] ?? 0);
if ($userId <= 0) {
    redirect('/admin/user_list.php');
}

// Fetch user data
$user_result = mysqli_query($conn, "SELECT id, fullname, email, role, created_at, password FROM users WHERE id=$userId");
$user = mysqli_fetch_assoc($user_result);
if (!$user) {
    redirect('/admin/user_list.php');
}

$message = '';
$error = '';

// Fetch profile picture
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'profile_picture'");
if (mysqli_num_rows($check_column) > 0) {
  $profile_result = mysqli_query($conn, "SELECT profile_picture FROM users WHERE id=$userId");
  $profile_data = mysqli_fetch_assoc($profile_result);
  $user['profile_picture'] = $profile_data['profile_picture'] ?? null;
} else {
  $user['profile_picture'] = null;
}

// Create uploads directory
$uploads_dir = __DIR__ . '/../uploads/profiles';
if (!is_dir($uploads_dir)) {
    mkdir($uploads_dir, 0755, true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // CSRF protection
  if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    $error = 'Token keamanan tidak valid. Coba muat ulang halaman.';
  }
    
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? 'user');
    $password = trim($_POST['password'] ?? '');
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
    } elseif (!in_array($role, ['admin', 'user'])) {
        $error = 'Role tidak valid';
    } elseif (!empty($password)) {
        if (strlen($password) < 6) {
            $error = 'Password minimal 6 karakter';
        } elseif ($password !== $password_confirm) {
            $error = 'Password dan konfirmasi password tidak sesuai';
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
            $stmt = mysqli_prepare($conn, "UPDATE users SET fullname=?, email=?, role=?, profile_picture=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "ssssi", $fullname, $email, $role, $profile_picture, $userId);
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = mysqli_prepare($conn, "UPDATE users SET fullname=?, email=?, role=?, password=?, profile_picture=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "sssssi", $fullname, $email, $role, $hashed_password, $profile_picture, $userId);
        }

        if (mysqli_stmt_execute($stmt)) {
            $message = '✅ Data user berhasil diperbarui';
            $user['fullname'] = $fullname;
            $user['email'] = $email;
            $user['role'] = $role;
            $user['profile_picture'] = $profile_picture;
        } else {
            $error = 'Gagal memperbarui data: ' . mysqli_error($conn);
        }
    }
}

$title = "Edit User #$userId";
require __DIR__ . '/../templates/header.php';

$profile_pic_url = $user['profile_picture'] ? base_path('/' . $user['profile_picture']) : '';
$initials = strtoupper(substr($user['fullname'] ?? 'U', 0, 1));
?>

<section class="profile-edit-section">
  <div class="profile-edit-wrapper">
    
    <!-- Back Button -->
    <a href="<?= base_path('/admin/user_list.php') ?>" class="back-link">
      <span>← Kembali ke Daftar User</span>
    </a>

    <!-- Page Title -->
    <div class="edit-page-header">
      <h1>Edit User - <?= e($user['fullname']) ?></h1>
      <p>Kelola data user #<?= (int)$user['id'] ?></p>
    </div>

    <!-- Messages -->
    <?php if (!empty($message)): ?>
      <div class="alert alert-success">
        <span class="alert-icon">✓</span>
        <?= $message ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="alert alert-error">
        <span class="alert-icon">!</span>
        <?= $error ?>
      </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="edit-form">
      <?= csrf_field() ?>
      
      <!-- Profile Picture Card -->
      <div class="edit-card">
        <h2 class="card-title">Foto Profil</h2>
          <p class="card-subtitle">Ubah foto profil user</p>

        <div class="pic-section">
          <div class="pic-preview">
            <?php if (!empty($profile_pic_url)): ?>
              <img src="<?= $profile_pic_url ?>" alt="<?= e($user['fullname']) ?>" class="preview-img">
            <?php else: ?>
              <div class="preview-empty">
                <p><?= $initials ?></p>
              </div>
            <?php endif; ?>
          </div>

          <div class="pic-upload">
            <input type="file" id="profile_picture" name="profile_picture" class="file-input" accept="image/*">
            <label for="profile_picture" class="upload-label">
              <div class="upload-content">
                <p class="upload-title">Klik untuk upload</p>
                <p class="upload-hint">atau drag & drop</p>
              </div>
            </label>
            <p class="upload-info">JPG, PNG, GIF, WebP (Max 5MB)</p>
          </div>
        </div>
      </div>

      <!-- User Information -->
      <div class="edit-card">
        <h2 class="card-title">Informasi User</h2>
        <p class="card-subtitle">Kelola data user</p>

        <div class="form-group">
          <label for="fullname" class="form-label">Nama Lengkap <span class="required">*</span></label>
          <div class="input-wrapper">
            <input type="text" id="fullname" name="fullname" class="form-input" value="<?= e($user['fullname'] ?? '') ?>" required minlength="3">
          </div>
        </div>

        <div class="form-group">
          <label for="email" class="form-label">Email <span class="required">*</span></label>
          <div class="input-wrapper">
            <input type="email" id="email" name="email" class="form-input" value="<?= e($user['email'] ?? '') ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label for="role" class="form-label">Role <span class="required">*</span></label>
          <div class="input-wrapper">
            <select name="role" id="role" class="form-input" required>
              <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>👤 User</option>
              <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>👨‍💼 Admin</option>
            </select>
          </div>
        </div>

        <div class="info-boxes">
          <div class="info-box">
            <span class="box-label">🆔 User ID</span>
            <span class="box-value">#<?= (int)$user['id'] ?></span>
          </div>
          <div class="info-box">
            <span class="box-label">📅 Terdaftar</span>
            <span class="box-value"><?= date('d M Y, H:i', strtotime($user['created_at'] ?? now())) ?></span>
          </div>
        </div>
      </div>

      <!-- Password Change -->
      <div class="edit-card">
        <h2 class="card-title">Ubah Password</h2>
        <p class="card-subtitle">Biarkan kosong jika tidak ingin mengubah</p>

        <div class="form-group">
          <label for="password" class="form-label">Password Baru</label>
          <div class="input-wrapper">
            <input type="password" id="password" name="password" class="form-input" minlength="6" placeholder="Minimal 6 karakter">
          </div>
          <p class="form-hint">Biarkan kosong untuk tidak mengubah password</p>
        </div>

        <div class="form-group">
          <label for="password_confirm" class="form-label">Konfirmasi Password</label>
          <div class="input-wrapper">
            <input type="password" id="password_confirm" name="password_confirm" class="form-input" minlength="6" placeholder="Ulangi password baru">
          </div>
          <p class="form-hint">Pastikan kedua password sama</p>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-large">
          <span>💾</span> Simpan Perubahan
        </button>
        <a href="<?= base_path('/admin/user_list.php') ?>" class="btn btn-secondary btn-large">
          <span>❌</span> Batal
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
  if (passwordInput.value || confirmInput.value) {
    if (passwordInput.value !== confirmInput.value) {
      e.preventDefault();
      alert('Password dan konfirmasi password tidak sesuai!');
      confirmInput.focus();
    }
  }
});
</script>

<?php require __DIR__ . '/../templates/footer.php'; ?>
