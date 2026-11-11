<?php
// ==============================================================================
// FILE: index.php
// KETERANGAN: Portal Utama UUK (Public Home, Login, Register, Siswa & Admin View)
// ==============================================================================
session_start();
require_once 'konek_database.php';

$flash_message = null;
$flash_type = 'info';

// ------------------------------------------------------------------------------
// PENANGANAN FORM POST (LOGIN, REGISTER, LOGOUT, RESET PASSWORD, USER MANAGEMENT)
// ------------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // 1. PROSES LOGIN
    if ($action === 'login') {
        $username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
        $password = trim($_POST['password']);

        $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
        $res = mysqli_query($koneksi, $query);

        if ($res && mysqli_num_rows($res) > 0) {
            $user = mysqli_fetch_assoc($res);
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'role' => $user['role'],
                'user_class' => $user['user_class'],
                'nis' => $user['nis']
            ];
            $flash_message = "Selamat datang kembali, " . htmlspecialchars($user['full_name']) . "!";
            $flash_type = 'success';
        } else {
            $flash_message = "Username atau password tidak valid!";
            $flash_type = 'error';
        }
    }

    // 2. PROSES REGISTRASI SISWA
    elseif ($action === 'register') {
        $full_name  = mysqli_real_escape_string($koneksi, trim($_POST['full_name']));
        $nis        = mysqli_real_escape_string($koneksi, trim($_POST['nis']));
        $user_class = mysqli_real_escape_string($koneksi, $_POST['user_class']);
        $username   = mysqli_real_escape_string($koneksi, strtolower(trim($_POST['username'])));
        $password   = mysqli_real_escape_string($koneksi, trim($_POST['password']));

        // Cek username unik
        $check = mysqli_query($koneksi, "SELECT id FROM users WHERE username = '$username'");
        if (mysqli_num_rows($check) > 0) {
            $flash_message = "Username '$username' sudah terdaftar! Gunakan username lain.";
            $flash_type = 'error';
        } else {
            $ins = "INSERT INTO users (username, password, full_name, role, user_class, nis) 
                    VALUES ('$username', '$password', '$full_name', 'siswa', '$user_class', '$nis')";
            if (mysqli_query($koneksi, $ins)) {
                $flash_message = "Registrasi akun siswa berhasil! Silakan login.";
                $flash_type = 'success';
            } else {
                $flash_message = "Gagal registrasi: " . mysqli_error($koneksi);
                $flash_type = 'error';
            }
        }
    }

    // 3. PROSES RESET PASSWORD SISWA (ADMIN ONLY)
    elseif ($action === 'reset_password' && isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
        $user_id      = (int)$_POST['user_id'];
        $new_password = mysqli_real_escape_string($koneksi, trim($_POST['new_password']));

        if (!empty($new_password)) {
            $up = "UPDATE users SET password = '$new_password' WHERE id = $user_id";
            if (mysqli_query($koneksi, $up)) {
                $flash_message = "Password pengguna berhasil direset!";
                $flash_type = 'success';
            }
        }
    }

    // 4. PROSES TAMBAH USER BARU (ADMIN ONLY)
    elseif ($action === 'add_user' && isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
        $full_name  = mysqli_real_escape_string($koneksi, trim($_POST['full_name']));
        $role       = mysqli_real_escape_string($koneksi, $_POST['role']);
        $nis        = mysqli_real_escape_string($koneksi, trim($_POST['nis']));
        $user_class = ($role === 'admin') ? 'Guru/Penguji' : mysqli_real_escape_string($koneksi, $_POST['user_class']);
        $username   = mysqli_real_escape_string($koneksi, strtolower(trim($_POST['username'])));
        $password   = mysqli_real_escape_string($koneksi, trim($_POST['password']));

        $ins = "INSERT INTO users (username, password, full_name, role, user_class, nis) 
                VALUES ('$username', '$password', '$full_name', '$role', '$user_class', '$nis')";
        if (mysqli_query($koneksi, $ins)) {
            $flash_message = "User baru ($full_name) berhasil ditambahkan!";
            $flash_type = 'success';
        } else {
            $flash_message = "Username sudah ada atau data tidak valid!";
            $flash_type = 'error';
        }
    }

    // 5. PROSES HAPUS USER (ADMIN ONLY)
    elseif ($action === 'delete_user' && isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
        $user_id = (int)$_POST['user_id'];
        if ($user_id !== $_SESSION['user']['id']) {
            mysqli_query($koneksi, "DELETE FROM users WHERE id = $user_id");
            $flash_message = "User berhasil dihapus.";
            $flash_type = 'info';
        }
    }
}

// LOGOUT ACTION VIA GET
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['user']);
    session_destroy();
    header("Location: index.php");
    exit();
}

// ------------------------------------------------------------------------------
// AMBIL DATA UJIAN & USER DARI DATABASE
// ------------------------------------------------------------------------------
$query_exams = "SELECT * FROM exams ORDER BY exam_date ASC";
$res_exams   = mysqli_query($koneksi, $query_exams);
$exams       = [];
while ($row = mysqli_fetch_assoc($res_exams)) {
    $exams[] = $row;
}

$query_users = "SELECT * FROM users ORDER BY role ASC, full_name ASC";
$res_users   = mysqli_query($koneksi, $query_users);
$users_list  = [];
while ($row = mysqli_fetch_assoc($res_users)) {
    $users_list[] = $row;
}

$current_user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uji Unit Kompetensi (UUK) - Portal SMK TKJ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="h-full flex flex-col font-['Inter'] text-slate-700 bg-slate-50 antialiased">

    <!-- NAVIGATION BAR HEADER -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <!-- Logo & Brand Title -->
                <a href="index.php" class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-slate-800 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-md">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div>
                        <span class="text-lg font-bold text-slate-800 tracking-tight block leading-none">Uji Unit Kompetensi</span>
                        <span class="text-xs text-slate-500 font-medium">Portal Ujian XI SMK TKJ</span>
                    </div>
                </a>

                <!-- Nav Links & Profile State -->
                <div class="flex items-center space-x-3">
                    <?php if ($current_user): ?>
                        <span class="text-xs font-semibold px-3 py-1.5 rounded-full <?= $current_user['role'] === 'admin' ? 'bg-slate-800 text-amber-300' : 'bg-emerald-100 text-emerald-800' ?>">
                            <i class="fa-solid <?= $current_user['role'] === 'admin' ? 'fa-user-shield' : 'fa-user-graduate' ?> mr-1"></i>
                            <?= htmlspecialchars($current_user['full_name']) ?> (<?= strtoupper($current_user['role']) ?>)
                        </span>
                        <a href="index.php?action=logout" class="px-3.5 py-1.5 rounded-xl text-xs font-medium text-rose-600 hover:bg-rose-50 border border-rose-200 transition-colors">
                            <i class="fa-solid fa-right-from-bracket"></i> Keluar
                        </a>
                    <?php else: ?>
                        <button onclick="openModal('loginModal')" class="px-3.5 py-2 rounded-xl text-sm font-medium text-slate-700 hover:text-indigo-600 hover:bg-slate-100 transition-colors">
                            <i class="fa-solid fa-right-to-bracket text-xs mr-1"></i> Login
                        </button>
                        <button onclick="openModal('registerModal')" class="px-4 py-2 rounded-xl text-sm font-medium bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition-colors">
                            <i class="fa-solid fa-user-plus text-xs mr-1"></i> Registrasi Siswa
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- NOTIFIKASI FLASH MESSAGE -->
    <?php if ($flash_message): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <div class="p-4 rounded-2xl flex items-center gap-3 text-sm font-medium <?= $flash_type === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : ($flash_type === 'error' ? 'bg-rose-50 border border-rose-200 text-rose-800' : 'bg-slate-100 border border-slate-200 text-slate-800') ?>">
                <i class="fa-solid <?= $flash_type === 'success' ? 'fa-circle-check text-emerald-600' : 'fa-circle-exclamation text-rose-600' ?> text-lg"></i>
                <span><?= $flash_message ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- MAIN CONTENT CONTAINER -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- ==================================================================== -->
        <!-- VIEW 1: JIKA BELUM LOGIN (HALAMAN PUBLIK / HOME)                     -->
        <!-- ==================================================================== -->
        <?php if (!$current_user): ?>
            <section class="space-y-10">
                <!-- Hero Banner -->
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-800 via-slate-700 to-indigo-950 text-white p-8 sm:p-12 shadow-xl">
                    <div class="relative z-10 max-w-2xl space-y-4">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-200 border border-indigo-400/30">
                            <i class="fa-solid fa-circle-check text-emerald-400"></i> Sistem Evaluasi Resmi TKJ
                        </span>
                        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight leading-tight text-white">
                            Portal Uji Unit Kompetensi (UUK) Kelas XI SMK
                        </h1>
                        <p class="text-slate-300 text-base sm:text-lg leading-relaxed font-light">
                            Platform integrasi pelaksanaan evaluasi praktek keahlian Teknik Komputer dan Jaringan. Menjamin transparansi penjadwalan, validasi kompetensi, dan akuntabilitas hasil penilaian.
                        </p>
                        <div class="pt-2 flex flex-wrap gap-4">
                            <button onclick="openModal('loginModal')" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-xl shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2">
                                <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Portal
                            </button>
                            <button onclick="openModal('registerModal')" class="px-6 py-3 bg-white/10 hover:bg-white/20 text-white font-medium rounded-xl border border-white/20 transition-all flex items-center gap-2 backdrop-blur-sm">
                                <i class="fa-solid fa-user-plus"></i> Registrasi Siswa
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Info Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                        <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-xl font-bold mb-4">
                            <i class="fa-solid fa-server"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-800 mb-2">Modul Administrasi Server</h3>
                        <p class="text-slate-600 text-sm leading-relaxed mb-4">
                            Subnetting /27, CentOS 9 Stream, DNS BIND9, Web Server Apache, MariaDB CRUD, dan Git Repository.
                        </p>
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-md bg-slate-100 text-slate-700">Waktu: 180 Menit</span>
                    </div>

                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold mb-4">
                            <i class="fa-solid fa-network-wired"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-800 mb-2">Modul Infrastruktur Jaringan</h3>
                        <p class="text-slate-600 text-sm leading-relaxed mb-4">
                            VLSM multi-cabang (Malang, Banyuwangi, Pacitan, Surabaya), Cisco Static Routing, serta DNS/Web access.
                        </p>
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-md bg-indigo-50 text-indigo-700">Waktu: 180 Menit</span>
                    </div>

                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold mb-4">
                            <i class="fa-solid fa-clipboard-check"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-800 mb-2">Kriteria Kelulusan</h3>
                        <p class="text-slate-600 text-sm leading-relaxed mb-4">
                            Peserta dinyatakan **KOMPETEN** apabila mencapai nilai minimal 75.00 pada gabungan penilaian praktikum.
                        </p>
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-md bg-emerald-50 text-emerald-700">KKM: 75.00 Poin</span>
                    </div>
                </div>

                <!-- Public Exam Table -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-bold text-slate-800">Jadwal Ujian Terjadwal</h2>
                            <p class="text-sm text-slate-500">Daftar pelaksanaan Uji Unit Kompetensi untuk seluruh kelas XI TKJ</p>
                        </div>
                        <button onclick="openModal('loginModal')" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                            Masuk untuk detail <i class="fa-solid fa-arrow-right text-xs"></i>
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600">
                            <thead class="bg-slate-50 text-slate-700 font-semibold uppercase text-xs tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-3.5">Nama Ujian / Modul</th>
                                    <th class="px-6 py-3.5">Target Kelas</th>
                                    <th class="px-6 py-3.5">Waktu Pelaksanaan</th>
                                    <th class="px-6 py-3.5">Penguji</th>
                                    <th class="px-6 py-3.5 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($exams as $ex): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 font-semibold text-slate-800"><?= htmlspecialchars($ex['title']) ?></td>
                                        <td class="px-6 py-4"><span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 text-xs"><?= htmlspecialchars($ex['target_class']) ?></span></td>
                                        <td class="px-6 py-4 text-xs text-slate-500"><?= date('d M Y - H:i', strtotime($ex['exam_date'])) ?> (<?= $ex['duration'] ?>m)</td>
                                        <td class="px-6 py-4 text-xs text-slate-600"><?= htmlspecialchars($ex['examiner']) ?></td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-3 py-1 rounded-full text-xs font-bold <?= $ex['status'] === 'Berlangsung' ? 'bg-emerald-100 text-emerald-800' : ($ex['status'] === 'Mendatang' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600') ?>">
                                                <?= $ex['status'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        <!-- ==================================================================== -->
        <!-- VIEW 2: JIKA LOGIN SEBAGAI SISWA                                    -->
        <!-- ==================================================================== -->
        <?php elseif ($current_user['role'] === 'siswa'): ?>
            <section class="space-y-6">
                <!-- Banner Siswa (Sage/Teal Theme) -->
                <div class="bg-gradient-to-r from-teal-800 via-slate-800 to-emerald-900 rounded-3xl p-6 sm:p-8 text-white shadow-lg flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 bg-emerald-400/20 border border-emerald-400/30 text-emerald-300 text-xs font-semibold rounded-full uppercase tracking-wider">
                                <i class="fa-solid fa-user-graduate"></i> Portal Siswa
                            </span>
                            <span class="text-xs text-slate-300">Kelas: <?= htmlspecialchars($current_user['user_class']) ?> (NIS: <?= htmlspecialchars($current_user['nis']) ?>)</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-white">
                            Selamat Datang, <?= htmlspecialchars($current_user['full_name']) ?>
                        </h2>
                        <p class="text-teal-100 text-sm font-light">Pantau jadwal dan informasi Uji Unit Kompetensi keahlian Anda</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md px-5 py-3 rounded-2xl border border-white/20 text-center">
                        <span class="text-xs text-slate-300 uppercase tracking-wider block">Status Persiapan</span>
                        <span class="text-sm font-bold text-emerald-300"><i class="fa-solid fa-circle-check"></i> Siap Melaksanakan</span>
                    </div>
                </div>

                <!-- Grid Kartu Jadwal Ujian Siswa -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($exams as $ex): ?>
                        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between space-y-4">
                            <div class="space-y-3">
                                <div class="flex justify-between items-start gap-2">
                                    <span class="px-2.5 py-1 bg-teal-50 text-teal-700 font-semibold rounded-md text-xs"><?= htmlspecialchars($ex['target_class']) ?></span>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold <?= $ex['status'] === 'Berlangsung' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                                        <?= $ex['status'] ?>
                                    </span>
                                </div>
                                <h4 class="font-bold text-slate-800 text-lg leading-snug"><?= htmlspecialchars($ex['title']) ?></h4>
                                <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed"><?= htmlspecialchars($ex['description']) ?></p>
                            </div>
                            <div class="pt-3 border-t border-slate-100 space-y-2 text-xs text-slate-500">
                                <div><i class="fa-regular fa-clock text-teal-600 mr-1"></i> Waktu: <?= date('d M Y - H:i', strtotime($ex['exam_date'])) ?> (<?= $ex['duration'] ?> Min)</div>
                                <div><i class="fa-solid fa-user-check text-teal-600 mr-1"></i> Penguji: <?= htmlspecialchars($ex['examiner']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

        <!-- ==================================================================== -->
        <!-- VIEW 3: JIKA LOGIN SEBAGAI ADMIN (GURU PENGUJI)                      -->
        <!-- ==================================================================== -->
        <?php elseif ($current_user['role'] === 'admin'): ?>
            <section class="space-y-6">
                <!-- Header Banner Admin (Dark Slate Theme) -->
                <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 rounded-3xl p-6 sm:p-8 text-white shadow-lg flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 bg-amber-400/20 border border-amber-400/30 text-amber-300 text-xs font-semibold rounded-full uppercase tracking-wider">
                                <i class="fa-solid fa-user-shield"></i> Portal Guru / Penguji
                            </span>
                            <span class="text-xs text-slate-400">Hak Akses Penuh</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-white">Kelola Ujian & Data Siswa</h2>
                        <p class="text-slate-300 text-sm font-light">Kelola modul UUK, daftar siswa terdaftar, dan reset password siswa</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="input.php" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-xl shadow-md transition-all flex items-center gap-2 text-sm">
                            <i class="fa-solid fa-plus"></i> Tambah Ujian Baru
                        </a>
                        <button onclick="openModal('userMgmtModal')" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-medium rounded-xl shadow-md transition-all flex items-center gap-2 text-sm">
                            <i class="fa-solid fa-users-gear"></i> Kelola User & Reset Password
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Ujian</p>
                            <h4 class="text-2xl font-bold text-slate-800 mt-1"><?= count($exams) ?></h4>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-list-check"></i>
                        </div>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Terdaftar Siswa</p>
                            <h4 class="text-2xl font-bold text-indigo-600 mt-1"><?= count($users_list) ?></h4>
                            <span class="text-[10px] text-emerald-600 font-medium">Klik kelola user &rarr;</span>
                        </div>
                        <div onclick="openModal('userMgmtModal')" class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 cursor-pointer flex items-center justify-center text-lg">
                            <i class="fa-solid fa-users"></i>
                        </div>
                    </div>
                </div>

                <!-- Tabel Manajemen Ujian CRUD -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-table-list text-indigo-600"></i> Daftar Manajemen UUK
                        </h3>
                        <a href="input.php" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-medium transition-all shadow-sm flex items-center gap-1.5">
                            <i class="fa-solid fa-plus"></i> Tambah Ujian
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600">
                            <thead class="bg-slate-100/70 text-slate-700 font-semibold uppercase text-xs tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4">Nama Ujian / Modul</th>
                                    <th class="px-6 py-4">Target Kelas</th>
                                    <th class="px-6 py-4">Jadwal & Durasi</th>
                                    <th class="px-6 py-4">Penguji</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-center">Aksi (CRUD)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($exams as $ex): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-slate-800"><?= htmlspecialchars($ex['title']) ?></div>
                                            <div class="text-xs text-slate-500 line-clamp-1"><?= htmlspecialchars($ex['description']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 text-xs"><span class="px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-700 font-medium"><?= htmlspecialchars($ex['target_class']) ?></span></td>
                                        <td class="px-6 py-4 text-xs text-slate-600 font-mono"><?= date('d M Y - H:i', strtotime($ex['exam_date'])) ?><br><span class="text-slate-400"><?= $ex['duration'] ?> Min</span></td>
                                        <td class="px-6 py-4 text-xs text-slate-700 font-medium"><?= htmlspecialchars($ex['examiner']) ?></td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-3 py-1 rounded-full text-xs font-bold <?= $ex['status'] === 'Berlangsung' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                                                <?= $ex['status'] ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center space-x-2">
                                                <a href="update.php?id=<?= $ex['id'] ?>" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit Ujian">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <a href="delete.php?id=<?= $ex['id'] ?>" onclick="return confirm('Hapus ujian ini?');" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Ujian">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        <?php endif; ?>

    </main>

    <!-- ==================================================================== -->
    <!-- MODAL POPUPS: LOGIN & REGISTRATION                                   -->
    <!-- ==================================================================== -->

    <!-- MODAL LOGIN -->
    <div id="loginModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-100 p-6 space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2"><i class="fa-solid fa-lock text-indigo-600"></i> Masuk Akun</h3>
                <button onclick="closeModal('loginModal')" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <!-- Demo Quick Fill Buttons -->
            <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200 text-xs space-y-2">
                <span class="font-semibold text-slate-500 block text-center">Uji Coba Cepat (Click to fill):</span>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="fillLogin('admin','admin123')" class="py-1.5 bg-white border border-slate-200 rounded-lg font-medium text-slate-700 hover:bg-slate-100">
                        <i class="fa-solid fa-user-shield text-indigo-600"></i> Admin (Guru)
                    </button>
                    <button type="button" onclick="fillLogin('siswa','siswa123')" class="py-1.5 bg-white border border-slate-200 rounded-lg font-medium text-slate-700 hover:bg-slate-100">
                        <i class="fa-solid fa-user-graduate text-emerald-600"></i> Siswa
                    </button>
                </div>
            </div>

            <form action="index.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="login">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Username</label>
                    <input type="text" id="loginUsername" name="username" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Password</label>
                    <input type="password" id="loginPassword" name="password" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>
                <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl shadow-lg transition-all text-sm">
                    Masuk Sistem
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL REGISTRASI SISWA -->
    <div id="registerModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-100 p-6 space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2"><i class="fa-solid fa-user-plus text-emerald-600"></i> Registrasi Siswa</h3>
                <button onclick="closeModal('registerModal')" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form action="index.php" method="POST" class="space-y-3">
                <input type="hidden" name="action" value="register">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Nama Lengkap</label>
                    <input type="text" name="full_name" required placeholder="Budi Santoso" class="w-full px-4 py-2 rounded-xl border border-slate-200 text-sm">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">NIS / Absen</label>
                        <input type="text" name="nis" required placeholder="222301" class="w-full px-4 py-2 rounded-xl border border-slate-200 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Kelas</label>
                        <select name="user_class" class="w-full px-4 py-2 rounded-xl border border-slate-200 text-sm bg-white">
                            <option value="XI TKJ 1">XI TKJ 1</option>
                            <option value="XI TKJ 2">XI TKJ 2</option>
                            <option value="XI TKJ 3">XI TKJ 3</option>
                            <option value="XI TKJ 4">XI TKJ 4</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Username Baru</label>
                    <input type="text" name="username" required placeholder="budisantoso" class="w-full px-4 py-2 rounded-xl border border-slate-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Password</label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-2 rounded-xl border border-slate-200 text-sm">
                </div>
                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl shadow-lg text-sm">
                    Buat Akun Siswa
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL KELOLA USER & RESET PASSWORD (ADMIN ONLY) -->
    <?php if ($current_user && $current_user['role'] === 'admin'): ?>
        <div id="userMgmtModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden">
            <div class="bg-white rounded-3xl max-w-4xl w-full shadow-2xl overflow-hidden border border-slate-100 flex flex-col max-h-[85vh]">
                <div class="bg-slate-900 px-6 py-4 text-white flex justify-between items-center">
                    <h3 class="font-bold text-lg flex items-center gap-2"><i class="fa-solid fa-users-gear text-emerald-400"></i> Kelola User & Reset Password Siswa</h3>
                    <button onclick="closeModal('userMgmtModal')" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark text-xl"></i></button>
                </div>
                <div class="p-6 overflow-y-auto space-y-4">
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full text-left text-sm text-slate-600">
                            <thead class="bg-slate-100 text-slate-700 uppercase text-xs font-semibold">
                                <tr>
                                    <th class="px-4 py-3">Nama Lengkap</th>
                                    <th class="px-4 py-3">Username</th>
                                    <th class="px-4 py-3">Kelas / NIS</th>
                                    <th class="px-4 py-3 text-center">Role</th>
                                    <th class="px-4 py-3 text-center">Reset Password</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($users_list as $u): ?>
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-slate-800"><?= htmlspecialchars($u['full_name']) ?></td>
                                        <td class="px-4 py-3 font-mono text-xs text-indigo-600"><?= htmlspecialchars($u['username']) ?></td>
                                        <td class="px-4 py-3 text-xs"><?= htmlspecialchars($u['user_class']) ?> (<?= htmlspecialchars($u['nis']) ?>)</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2 py-0.5 rounded text-xs font-bold <?= $u['role'] === 'admin' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' ?>">
                                                <?= strtoupper($u['role']) ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <form action="index.php" method="POST" class="flex items-center justify-center space-x-1">
                                                <input type="hidden" name="action" value="reset_password">
                                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                <input type="text" name="new_password" required placeholder="pass baru" class="w-24 px-2 py-1 border border-slate-200 text-xs rounded font-mono">
                                                <button type="submit" class="px-2.5 py-1 bg-amber-600 text-white rounded text-xs hover:bg-amber-700">Set</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script>
        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
        function fillLogin(u, p) {
            document.getElementById('loginUsername').value = u;
            document.getElementById('loginPassword').value = p;
        }
    </script>
</body>
</html>