<?php
// ==============================================================================
// FILE: konek_database.php
// KETERANGAN: Membuka koneksi ke Server MariaDB / MySQL pada CentOS 9
// ==============================================================================

$host     = "localhost";
$username = "root";
$password = "";              // Sesuaikan password MariaDB server Anda jika ada
$database = "db_uuk_tkj";    // Nama database sesuai skema UUK

// Membuka koneksi menggunakan extension mysqli
$koneksi = mysqli_connect($host, $username, $password, $database);

// Memeriksa status koneksi
if (!$koneksi) {
    die("<div style='color:red; font-family:sans-serif; padding:20px;'>
            <h3>Koneksi Database Gagal!</h3>
            <p>Error: " . mysqli_connect_error() . "</p>
            <p>Pastikan MariaDB service sudah berjalan dan database <b>db_uuk_tkj</b> sudah dibuat.</p>
         </div>");
}

// Set charset ke utf8 untuk mendukung karakter khusus
mysqli_set_charset($koneksi, "utf8");
?>