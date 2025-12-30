<?php
// setup_database_and_admin.php - Buat database dan akun admin

$host = "localhost";
$user = "root";
$pass = "";

// 1. Koneksi tanpa database
echo "1. Menghubungkan ke MySQL...\n";
$conn = mysqli_connect($host, $user, $pass);
if (!$conn) {
    die("❌ Error koneksi: " . mysqli_connect_error());
}
echo "✓ Terkoneksi\n";

// 2. Buat database
echo "\n2. Membuat database 'projek_pwd'...\n";
$sql = "CREATE DATABASE IF NOT EXISTS projek_pwd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (mysqli_query($conn, $sql)) {
    echo "✓ Database berhasil dibuat\n";
} else {
    die("❌ Error: " . mysqli_error($conn));
}

// 3. Pilih database
echo "\n3. Memilih database...\n";
mysqli_select_db($conn, "projek_pwd");
if (mysqli_error($conn)) {
    die("❌ Error: " . mysqli_error($conn));
}
echo "✓ Database terpilih\n";

// 4. Buat tabel users
echo "\n4. Membuat tabel users...\n";
$sql_users = "CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fullname VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  profile_picture VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB";

if (mysqli_query($conn, $sql_users)) {
    echo "✓ Tabel users berhasil dibuat\n";
} else {
    if (strpos(mysqli_error($conn), "already exists") !== false) {
        echo "✓ Tabel users sudah ada\n";
    } else {
        die("❌ Error: " . mysqli_error($conn));
    }
}

// 5. Buat akun admin
echo "\n5. Membuat akun admin...\n";
$fullname = "Admin";
$email = "admin@demo.com";
$plainPassword = "nanda123";
$hash = password_hash($plainPassword, PASSWORD_BCRYPT);

$q = mysqli_prepare($conn, "INSERT INTO users(fullname,email,password,role) VALUES(?,?,?,'admin')");
mysqli_stmt_bind_param($q, "sss", $fullname, $email, $hash);

if (mysqli_stmt_execute($q)) {
    echo "✓ Akun admin berhasil dibuat!\n";
    echo "\n📧 Email: admin@demo.com\n";
    echo "🔐 Password: nanda123\n";
} else {
    if (strpos(mysqli_error($conn), "Duplicate entry") !== false) {
        echo "ℹ️  Akun admin sudah ada\n";
        echo "📧 Email: admin@demo.com\n";
        echo "🔐 Password: nanda123\n";
    } else {
        echo "❌ Error: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
echo "\n✓ Setup selesai!\n";
?>
