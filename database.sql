-- =========================================
-- DATABASE: projek_pwd
-- Sistem Reservasi Ruangan (Admin/User)
-- =========================================

CREATE DATABASE IF NOT EXISTS projek_pwd
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE projek_pwd;

-- =========================================
-- 1) USERS (admin/user)
-- =========================================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fullname VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  profile_picture VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================
-- 2) RUANGAN (multi-room + kapasitas)
-- =========================================
DROP TABLE IF EXISTS ruangan;
CREATE TABLE ruangan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(80) NOT NULL UNIQUE,
  lokasi VARCHAR(120) NULL,
  kapasitas INT NOT NULL DEFAULT 1,
  fasilitas TEXT NULL,
  status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================
-- 3) RESERVASI (booking)
-- status:
--  - pending   : menunggu persetujuan admin
--  - approved  : disetujui
--  - rejected  : ditolak
--  - canceled  : dibatalkan user
-- notified:
--  - 0 belum dikirim reminder
--  - 1 sudah dikirim reminder
-- =========================================
DROP TABLE IF EXISTS reservasi;
CREATE TABLE reservasi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  ruangan_id INT NOT NULL,
  tanggal DATE NOT NULL,
  waktu_mulai TIME NOT NULL,
  waktu_selesai TIME NOT NULL,
  jumlah_peserta INT NOT NULL DEFAULT 1,
  catatan VARCHAR(255) NULL,
  status ENUM('pending','approved','rejected','canceled') NOT NULL DEFAULT 'pending',
  notified TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_reservasi_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,

  CONSTRAINT fk_reservasi_ruangan
    FOREIGN KEY (ruangan_id) REFERENCES ruangan(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,

  INDEX idx_reservasi_ruangan_tanggal (ruangan_id, tanggal),
  INDEX idx_reservasi_user (user_id),
  INDEX idx_reservasi_status (status)
) ENGINE=InnoDB;

-- =========================================
-- 4) AUDIT LOG (aksi admin)
-- action contoh: approve, reject, edit_ruangan, delete_ruangan, dll
-- =========================================
DROP TABLE IF EXISTS audit_log;
CREATE TABLE audit_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT NOT NULL,
  action VARCHAR(50) NOT NULL,
  reservasi_id INT NULL,
  detail TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_audit_admin
    FOREIGN KEY (admin_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,

  CONSTRAINT fk_audit_reservasi
    FOREIGN KEY (reservasi_id) REFERENCES reservasi(id)
    ON DELETE SET NULL ON UPDATE CASCADE,

  INDEX idx_audit_admin (admin_id),
  INDEX idx_audit_action (action)
) ENGINE=InnoDB;

-- =========================================
-- 5) NOTIFIKASI (opsional - jika mau simpan notifikasi)
-- =========================================
DROP TABLE IF EXISTS notifikasi;
CREATE TABLE notifikasi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(120) NOT NULL,
  message VARCHAR(255) NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_notif_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,

  INDEX idx_notif_user (user_id),
  INDEX idx_notif_read (is_read)
) ENGINE=InnoDB;

-- =========================================
-- 6) VIEW (memudahkan join untuk jadwal)
-- =========================================
DROP VIEW IF EXISTS v_jadwal;
CREATE VIEW v_jadwal AS
SELECT
  r.id,
  r.tanggal,
  r.waktu_mulai,
  r.waktu_selesai,
  r.status,
  r.jumlah_peserta,
  u.fullname AS pemesan,
  u.email,
  ru.nama AS ruangan,
  ru.kapasitas,
  ru.lokasi
FROM reservasi r
JOIN users u ON u.id = r.user_id
JOIN ruangan ru ON ru.id = r.ruangan_id;

-- =========================================
-- 7) SEED DATA (contoh ruangan)
-- =========================================
INSERT INTO ruangan (nama, lokasi, kapasitas, fasilitas, status) VALUES
('Ruangan Standar', 'Lantai 1', 10, 'Proyektor, Whiteboard', 'aktif'),
('Ruangan VIP', 'Lantai 2', 20, 'TV, Proyektor, AC, Whiteboard', 'aktif'),
('Ruang Meeting A', 'Lantai 1', 8, 'Whiteboard', 'aktif'),
('Ruang Meeting B', 'Lantai 1', 12, 'Proyektor', 'aktif');


-- INSERT INTO users(fullname,email,password,role) VALUES
-- ('Admin', 'admin@demo.com', '$2y$10$xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 'admin');
-- =========================================
