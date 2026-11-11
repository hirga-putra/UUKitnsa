<?php
// ==============================================================================
// FILE: konek_database.php
// KETERANGAN: Membuka koneksi MariaDB & Otomatis Membuat Database/Tabel
// ==============================================================================

$host     = "localhost";
$username = "root";
$password = "";              // Sesuaikan password MariaDB server Anda jika ada
$database = "db_uuk_tkj";    // Nama database sesuai skema UUK

// Membuka koneksi ke MySQL/MariaDB server
$koneksi = mysqli_connect($host, $username, $password);

if (!$koneksi) {
    die("<div style='color:red; font-family:sans-serif; padding:20px;'>
            <h3>Koneksi MariaDB Server Gagal!</h3>
            <p>Error: " . mysqli_connect_error() . "</p>
            <p>Pastikan service MariaDB/MySQL sudah berjalan pada OS CentOS 9 Anda.</p>
         </div>");
}

// 1. Buat Database jika belum ada
mysqli_query($koneksi, "CREATE DATABASE IF NOT EXISTS $database");
mysqli_select_db($koneksi, $database);
mysqli_set_charset($koneksi, "utf8");

// 2. Buat Tabel `users` jika belum ada
$table_users = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'siswa') NOT NULL DEFAULT 'siswa',
    user_class VARCHAR(50) DEFAULT NULL,
    nis VARCHAR(30) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($koneksi, $table_users);

// 3. Buat Tabel `exams` jika belum ada
$table_exams = "CREATE TABLE IF NOT EXISTS exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    target_class VARCHAR(50) NOT NULL,
    duration INT NOT NULL,
    exam_date DATETIME NOT NULL,
    status ENUM('Mendatang', 'Berlangsung', 'Selesai') NOT NULL DEFAULT 'Mendatang',
    examiner VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($koneksi, $table_exams);

// 4. Seed Data Awal Pengguna jika tabel users kosong
$check_users = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM users");
$row_u = mysqli_fetch_assoc($check_users);
if ($row_u['cnt'] == 0) {
    mysqli_query($koneksi, "INSERT INTO users (username, password, full_name, role, user_class, nis) VALUES 
        ('admin', 'admin123', 'Tim Penguji TKJ', 'admin', 'Penguji', 'NIP19850101'),
        ('siswa', 'siswa123', 'Budi Santoso', 'siswa', 'XI TKJ 4', '222301'),
        ('ahmad', 'siswa123', 'Ahmad Rizky', 'siswa', 'XI TKJ 1', '222302'),
        ('citra', 'siswa123', 'Citra Lestari', 'siswa', 'XI TKJ 2', '222303')");
}

// 5. Seed Data Awal Ujian jika tabel exams kosong
$check_exams = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM exams");
$row_e = mysqli_fetch_assoc($check_exams);
if ($row_e['cnt'] == 0) {
    mysqli_query($koneksi, "INSERT INTO exams (title, target_class, duration, exam_date, status, examiner, description) VALUES 
        ('Modul 1: Administrasi Server (CentOS 9 & Services)', 'Semua XI TKJ', 180, '2026-11-10 08:00:00', 'Berlangsung', 'Tim Penguji Server TKJ', 'Instalasi CentOS 9 Stream pada VirtualBox, pengalokasian IP Subnetting ke-6 /27, Web Server Apache, MariaDB CRUD, Git clone repositori, dan DNS Server BIND9 (kelas-noabsen.net.id).'),
        ('Modul 2: Infrastruktur Jaringan (VLSM & Routing Static)', 'Semua XI TKJ', 180, '2026-11-12 08:00:00', 'Mendatang', 'Tim Penguji Jaringan TKJ', 'Desain alokasi VLSM IP 68.XX.86.128/23 untuk cabang Malang (100 host), Banyuwangi (50 host), Pacitan (28 host), Surabaya Gateway, Cisco IOS Static Routing, dan verifikasi web access.'),
        ('Evaluasi Pra-UUK: Troubleshooting Jaringan Lokal', 'XI TKJ 4', 120, '2026-11-05 09:30:00', 'Selesai', 'Guru Pendamping TKJ', 'Simulasi pemecahan masalah IP conflict, routing loop, dan DNS resolution error.')");
}
?>