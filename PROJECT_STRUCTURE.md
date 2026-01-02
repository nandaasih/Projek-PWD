# Struktur Proyek FINAL_P

## 📋 Ringkasan Proyek
Proyek ini adalah aplikasi web untuk manajemen reservasi ruangan dengan sistem autentikasi pengguna, admin dashboard, dan notifikasi.

---

## 📁 Struktur Direktori

```
FINAL_P/
│
├── 📄 File Root (Login & Setup)
│   ├── index.php                 # Halaman awal/landing page
│   ├── login.php                 # Halaman login
│   ├── register.php              # Halaman registrasi
│   ├── chat.php                  # Modul chat/komunikasi
│   ├── setup_complete.php        # Halaman setup selesai
│   ├── dashboard.php             # Dashboard utama
│   ├── reservasi_add.php         # Form tambah reservasi
│   ├── styles.css                # CSS global
│   ├── database.sql              # SQL database setup
│   └── SETUP_PROFILE_PICTURE.txt # Konfigurasi upload foto profil
│
├── 📁 /actions                   # Backend logic untuk form handling
│   ├── check_bentrok.php         # Cek bentrok jadwal
│   ├── login_post.php            # Proses login
│   ├── register_post.php         # Proses registrasi
│   ├── logout.php                # Proses logout
│   ├── forgot_password_post.php  # Proses lupa password
│   ├── reset_password_post.php   # Proses reset password
│   ├── resend_verification.php   # Kirim ulang verifikasi
│   ├── ruangan_create.php        # Create ruangan
│   ├── notif_mark_read.php       # Mark notifikasi sebagai read
│   ├── reject.php                # Reject reservasi
│   ├── user_delete.php           # Delete user
│   └── example_post.php          # File contoh
│
├── 📁 /admin                     # Admin panel
│   ├── index.php                 # Dashboard admin
│   ├── profil.php                # Profil admin
│   ├── profil_edit.php           # Edit profil admin
│   ├── approve.php               # Approve reservasi
│   ├── reject.php                # Reject reservasi
│   ├── user_list.php             # Daftar user
│   ├── user_edit.php             # Edit user
│   ├── user_delete.php           # Delete user
│   ├── user_stats.php            # Statistik user
│   ├── reservasi_list.php        # Daftar reservasi
│   ├── reservasi_view.php        # Detail reservasi
│   ├── ruangan_list.php          # Daftar ruangan
│   ├── ruangan_create.php        # Buat ruangan baru
│   ├── ruangan_tambah.php        # Form tambah ruangan
│   ├── ruangan_edit.php          # Edit ruangan
│   ├── ruangan_update.php        # Proses update ruangan
│   └── ruangan_hapus.php         # Hapus ruangan
│
├── 📁 /user                      # User panel
│   ├── dashboard.php             # User dashboard
│   ├── profil.php                # Profil user
│   ├── profil_view.php           # View profil user
│   ├── profil_edit.php           # Edit profil user
│   ├── reservasi_add.php         # Tambah reservasi
│   ├── reservasi_create.php      # Proses create reservasi
│   ├── reservasi_list_ajax.php   # AJAX list reservasi
│   ├── reservasi_view.php        # Detail reservasi
│   ├── reservasi_history.php     # Riwayat reservasi
│   ├── reservasi_delete.php      # Delete reservasi
│   ├── ruangan_list.php          # Daftar ruangan
│   ├── check_availability.php    # Cek ketersediaan ruangan
│   ├── cek_ketersediaan.php      # Cek ketersediaan (alias)
│   ├── forgot_password.php       # Form lupa password
│   ├── reset_password.php        # Form reset password
│   ├── delete_confirm.php        # Konfirmasi delete
│   └── example_form.php          # Form contoh
│
├── 📁 /includes                  # Helper & Utility functions
│   ├── auth.php                  # Autentikasi & session
│   ├── database.php              # Koneksi database
│   ├── helpers.php               # Helper functions
│   ├── logout.php                # Logout function
│   ├── mailer.php                # Email sending
│   └── mailer_config.php         # Email configuration
│
├── 📁 /assets                    # Frontend assets
│   ├── script.js                 # JavaScript global
│   ├── styles.css                # CSS global
│   ├── profile.css               # CSS profil
│   ├── dashboard.css             # CSS dashboard
│   ├── dashboard-fixes.css       # CSS dashboard fixes
│   └── images/                   # Folder gambar
│
├── 📁 /templates                 # Reusable template files
│   ├── header.php                # Header template
│   ├── footer.php                # Footer template
│   └── sidebar.php               # Sidebar template
│
├── 📁 /uploads                   # User-uploaded files
│   └── profiles/                 # Folder profile pictures
│
├── 📁 /migrations                # Database migrations
│   └── add_profile_picture.sql   # Migration untuk profile picture
│
└── 📁 /scripts                   # Database & setup scripts
    ├── setup_db.php              # Setup database script
    └── check_users.php           # Check users script
```

---

## 🔑 File-File Penting

### Autentikasi
- `includes/auth.php` - Core authentication logic
- `actions/login_post.php` - Login processing
- `actions/register_post.php` - Registration processing
- `actions/logout.php` - Logout handler

### Database
- `includes/database.php` - Database connection
- `database.sql` - Database schema
- `scripts/setup_db.php` - Database setup script
- `migrations/` - Database alterations

### User Interface
- `templates/header.php`, `templates/footer.php`, `templates/sidebar.php` - Layout templates
- `assets/` - CSS & JavaScript files
- `uploads/profiles/` - User profile pictures

---

## 🚀 Alur Aplikasi

### User Authentication Flow
1. User mengakses `index.php` atau `login.php`
2. Submit form ke `actions/login_post.php`
3. Validasi di `includes/auth.php`
4. Redirect ke `user/dashboard.php` atau error

### Reservasi Flow
1. User ke `user/reservasi_add.php`
2. Pilih ruangan dan tanggal
3. Check ketersediaan via `user/check_availability.php`
4. Submit ke `user/reservasi_create.php`
5. Admin review di `admin/reservasi_list.php`
6. Admin approve/reject

### Admin Panel
- Manage user di `/admin/user_*.php`
- Manage ruangan di `/admin/ruangan_*.php`
- Manage reservasi di `/admin/reservasi_*.php`

---

## 📝 Fitur Utama

✅ User Registration & Login
✅ Profile Management (Edit, Upload Photo)
✅ Room Management (Create, Read, Update, Delete)
✅ Reservation System (Create, View, History, Delete)
✅ Availability Check
✅ Admin Approval System
✅ Email Notifications
✅ Password Reset
✅ User Statistics
✅ Chat System

---

## 🔧 Teknologi

- **Backend**: PHP
- **Database**: MySQL/MariaDB
- **Frontend**: HTML, CSS, JavaScript
- **Email**: SMTP Mailer
- **Server**: XAMPP

---

## 📌 Setup & Installation

1. Copy project ke `/htdocs/FINAL_P`
2. Import `database.sql` ke MySQL
3. Run `scripts/setup_db.php`
4. Konfigurasi email di `includes/mailer_config.php`
5. Access via `http://localhost/FINAL_P`

