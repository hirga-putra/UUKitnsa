# PANDUAN DEPLOYMENT & INSTALASI PORTAL UUK (CENTOS 9 STREAM)

Dokumen ini berisi panduan teknis langkah demi langkah untuk menginstal, mengonfigurasi, dan menjalankan aplikasi **Portal Uji Unit Kompetensi (UUK)** pada server dengan Sistem Operasi **CentOS 9 Stream**.

---

## DAFTAR ISI
1. [Instalasi Web Server Apache (HTTP)](#1-cara-install-http-server-apache)
2. [Instalasi Database Server MariaDB](#2-cara-install-mariadb)
3. [Instalasi Version Control System (Git)](#3-cara-install-git)
4. [Clone Repositori dari GitHub](#4-melakukan-git-clone-repositori)
5. [Konfigurasi Database MariaDB](#5-konfigurasi-database)
6. [Konfigurasi File konek_database.php](#6-konfigurasi-file-konek_databasephp)
7. [Pengujian & Uji Coba pada Client](#7-uji-coba-pada-client)

---

## 1. CARA INSTALL HTTP SERVER (APACHE)

Gunakan perintah `dnf` untuk menginstal service Apache HTTP Server pada CentOS 9 Stream.

### Step 1: Install Paket HTTPD
```bash
sudo dnf install httpd php php-mysqlnd -y
```

### Step 2: Jalankan & Aktifkan Service HTTPD (Auto-start saat Booting)
```bash
sudo systemctl start httpd
sudo systemctl enable httpd
```

### Step 3: Cek Status Service HTTPD
```bash
sudo systemctl status httpd
```

### Step 4: Konfigurasi Firewall (Buka Port 80 HTTP)
```bash
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

---

## 2. CARA INSTALL MARIADB

MariaDB digunakan sebagai *Database Management System* (DBMS) untuk menyimpan data pengguna dan jadwal ujian UUK.

### Step 1: Install Paket MariaDB Server
```bash
sudo dnf install mariadb-server mariadb -y
```

### Step 2: Jalankan & Aktifkan Service MariaDB
```bash
sudo systemctl start mariadb
sudo systemctl enable mariadb
```

### Step 3: Pengamanan Instalasi MariaDB (Security Initialization)
Jalankan perintah pengamanan dasar untuk membuat password root MariaDB:
```bash
sudo mysql_secure_installation
```
* **Enter current password for root**: Tekan `Enter` (default kosong).
* **Switch to unix_socket authentication**: Ketik `n`.
* **Change the root password?**: Ketik `y` lalu masukkan password baru (misal: `password` atau biarkan kosong jika untuk kebutuhan lokal lab).
* **Remove anonymous users?**: Ketik `y`.
* **Disallow root login remotely?**: Ketik `y`.
* **Remove test database and access to it?**: Ketik `y`.
* **Reload privilege tables now?**: Ketik `y`.

---

## 3. CARA INSTALL GIT

Git diperlukan untuk menarik source code website UUK langsung dari repositori GitHub.

### Step 1: Install Paket Git
```bash
sudo dnf install git -y
```

### Step 2: Verifikasi Instalasi Git
```bash
git --version
```

---

## 4. MELAKUKAN GIT CLONE REPOSITORI

Tarik source code aplikasi dari GitHub langsung ke direktori publik Web Server Apache (`/var/www/html`).

### Step 1: Masuk ke Direktori Web DocumentRoot
```bash
cd /var/www/html
```

### Step 2: Hapus File Bawaan (Jika Ada) & Clone Repositori
```bash
sudo rm -rf *
sudo git clone https://github.com/hirga-putra/UUKitnsa .
```
*(Catatan: Tanda titik `.` di akhir perintah bertujuan agar file ter-clone langsung di dalam folder `/var/www/html` tanpa membuat folder tambahan).*

### Step 3: Atur Hak Akses File (Permission & SELinux)
```bash
sudo chown -R apache:apache /var/www/html/
sudo chmod -R 755 /var/www/html/
sudo chcon -R -t httpd_sys_content_rw_t /var/www/html/
```

---

## 5. KONFIGURASI DATABASE

Buat database dan persiapkan skema tabel yang dibutuhkan oleh aplikasi UUK.

### Step 1: Masuk ke CLI MariaDB
```bash
sudo mysql -u root -p
```
*(Masukkan password root MariaDB Anda).*

### Step 2: Eksekusi Perintah SQL
Jalankan perintah SQL berikut di dalam prompt `MariaDB [(none)]>`:

```sql
-- 1. Buat Database
CREATE DATABASE IF NOT EXISTS db_uuk_tkj;
USE db_uuk_tkj;

-- 2. Buat Tabel Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'siswa') NOT NULL DEFAULT 'siswa',
    user_class VARCHAR(50) DEFAULT NULL,
    nis VARCHAR(30) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Buat Tabel Exams
CREATE TABLE IF NOT EXISTS exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    target_class VARCHAR(50) NOT NULL,
    duration INT NOT NULL,
    exam_date DATETIME NOT NULL,
    status ENUM('Mendatang', 'Berlangsung', 'Selesai') NOT NULL DEFAULT 'Mendatang',
    examiner VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Insert Default Akun Admin & Siswa
INSERT INTO users (username, password, full_name, role, user_class, nis) VALUES
('admin', 'admin', 'Tim Penguji TKJ', 'admin', 'Penguji', 'NIP19850101'),
('siswa', 'siswa123', 'Budi Santoso', 'siswa', 'XI TKJ 4', '222301');

-- 5. Keluar dari MariaDB
EXIT;
```

---

## 6. KONFIGURASI FILE `konek_database.php`

Pastikan pengaturan koneksi PHP sesuai dengan kredensial MariaDB di server CentOS 9 Anda.

### Step 1: Buka File `konek_database.php`
```bash
sudo nano /var/www/html/konek_database.php
```

### Step 2: Sesuaikan Variabel Koneksi
Pastikan isi variabel disesuaikan dengan konfigurasi server Anda:

```php
<?php
$host     = "localhost";
$username = "root";
$password = "";           // Isi sesuai password root MariaDB Anda (kosongkan jika tanpa password)
$database = "db_uuk_tkj";

$koneksi = mysqli_connect($host, $username, $password, $database);

if (!$koneksi) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}
?>
```
*(Simpan file dengan menekan `Ctrl + O` lalu `Enter`, kemudian keluar dengan `Ctrl + X`).*

---

## 7. UJI COBA PADA CLIENT

Setelah seluruh instalasi selesai, lakukan pengujian koneksi dan aplikasi dari komputer Client (Laptop).

### Step 1: Pengaturan Network Client
1. Pastikan Client terhubung dalam satu jaringan yang sama via adapter **Host-Only Network**.
2. Isikan IP Address Client yang se-subnet dengan IP Server (sesuai hasil perhitungan IP Subnet ke-6 `/27`).
3. Lakukan tes konektivitas via Terminal / Command Prompt Client:
   ```cmd
   ping [IP-Server-CentOS]
   ```
   *Pastikan status respon adalah **Reply from [IP-Server-CentOS]**.*

### Step 2: Akses Web Browser Client
1. Buka aplikasi Web Browser (Google Chrome / Mozilla Firefox).
2. Ketikkan URL IP Server atau Domain lokal Anda:
   ```
   http://198.16.XX.YY/
   ```
   *atau*
   ```
   http://kelas-noabsen.net.id/
   ```

### Step 3: Pengujian Fitur Hak Akses (Login & CRUD)
* **Pengujian Akun Admin (Guru)**:
  * Username: `admin`
  * Password: `admin`
  * *Verifikasi*: Pastikan halaman menampilkan **Dashboard Guru/Penguji**, fitur **Tambah Ujian**, **Edit**, **Hapus (CRUD)**, serta fitur **Kelola User & Reset Password Siswa** berfungsi dengan lancar.
* **Pengujian Akun Siswa**:
  * Username: `siswa`
  * Password: `siswa123`
  * *Verifikasi*: Pastikan halaman menampilkan **Dashboard Siswa** berlatar tema *Sage Green* yang hanya menampilkan daftar jadwal UUK.
