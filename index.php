<?php
// ==============================================================================
// FILE: index.php
// KETERANGAN: Dashboard Utama Penjadwalan & Pengelolaan UUK TKJ
// ==============================================================================
require_once 'konek_database.php';

// Ambil data ujian dari database
$query = "SELECT * FROM exams ORDER BY exam_date ASC";
$result = mysqli_query($koneksi, $query);

// Hitung statistik singkat
$total_exams = 0;
$total_berlangsung = 0;
$total_mendatang = 0;
$total_selesai = 0;

$exams_data = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $exams_data[] = $row;
        $total_exams++;
        if ($row['status'] == 'Berlangsung') $total_berlangsung++;
        if ($row['status'] == 'Mendatang') $total_mendatang++;
        if ($row['status'] == 'Selesai') $total_selesai++;
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uji Unit Kompetensi (UUK) - Portal Ujian SMK TKJ</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="h-full flex flex-col font-[Inter] text-slate-700 bg-slate-50 antialiased">

    <!-- HEADER NAVIGATION -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-slate-800 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-md">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div>
                        <span class="text-lg font-bold text-slate-800 tracking-tight block leading-none">Uji Unit Kompetensi</span>
                        <span class="text-xs text-slate-500 font-medium">Portal Administrasi Server & Jaringan XI TKJ</span>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="input.php" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition-all shadow-md flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i> Tambah Ujian Baru
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- NOTIFIKASI HASIL AKSI (TOAST / ALERT) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
        <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] == 'success_add'): ?>
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-3 text-sm">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    <span><strong>Berhasil!</strong> Data ujian UUK baru berhasil ditambahkan ke database.</span>
                </div>
            <?php elseif ($_GET['status'] == 'success_update'): ?>
                <div class="p-4 bg-indigo-50 border border-indigo-200 text-indigo-800 rounded-2xl flex items-center gap-3 text-sm">
                    <i class="fa-solid fa-pen-to-square text-indigo-600 text-lg"></i>
                    <span><strong>Berhasil!</strong> Data modul ujian berhasil diperbarui.</span>
                </div>
            <?php elseif ($_GET['status'] == 'success_delete'): ?>
                <div class="p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl flex items-center gap-3 text-sm">
                    <i class="fa-solid fa-trash-can text-amber-600 text-lg"></i>
                    <span><strong>Berhasil!</strong> Data ujian telah dihapus dari sistem.</span>
                </div>
            <?php elseif ($_GET['status'] == 'error'): ?>
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center gap-3 text-sm">
                    <i class="fa-solid fa-circle-xmark text-rose-600 text-lg"></i>
                    <span><strong>Gagal!</strong> Terjadi kesalahan saat memproses data di server.</span>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- MAIN CONTENT CONTAINER -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- STATISTIK RINGKAS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Jadwal</p>
                    <h4 class="text-2xl font-bold text-slate-800 mt-1"><?= $total_exams ?></h4>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-list-check"></i>
                </div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sedang Berlangsung</p>
                    <h4 class="text-2xl font-bold text-emerald-600 mt-1"><?= $total_berlangsung ?></h4>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-play"></i>
                </div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Ujian Mendatang</p>
                    <h4 class="text-2xl font-bold text-amber-600 mt-1"><?= $total_mendatang ?></h4>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Telah Selesai</p>
                    <h4 class="text-2xl font-bold text-slate-500 mt-1"><?= $total_selesai ?></h4>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-check-double"></i>
                </div>
            </div>
        </div>

        <!-- TABEL DATA UJIAN (CRUD VIEW) -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-50/50">
                <div>
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-table-list text-indigo-600"></i> Daftar Modul Uji Unit Kompetensi
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Data tersambung secara riil dengan database MariaDB server</p>
                </div>
                <a href="input.php" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-medium transition-all shadow-sm flex items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i> Tambah Ujian
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-100/70 text-slate-700 font-semibold uppercase text-xs tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">No</th>
                            <th class="px-6 py-4">Nama Ujian / Modul</th>
                            <th class="px-6 py-4">Target Kelas</th>
                            <th class="px-6 py-4">Jadwal & Durasi</th>
                            <th class="px-6 py-4">Penguji</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Aksi (CRUD)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($exams_data)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-slate-400">
                                    <i class="fa-solid fa-folder-open text-3xl mb-2 block"></i>
                                    Belum ada data ujian. Klik <b>"Tambah Ujian Baru"</b> untuk menginput.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($exams_data as $exam): ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-slate-400 text-xs"><?= $no++ ?></td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-800"><?= htmlspecialchars($exam['title']) ?></div>
                                        <div class="text-xs text-slate-500 line-clamp-1 mt-0.5"><?= htmlspecialchars($exam['description']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-xs">
                                        <span class="px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-700 font-medium border border-indigo-100">
                                            <?= htmlspecialchars($exam['target_class']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-mono text-slate-600">
                                        <?= date('d M Y - H:i', strtotime($exam['exam_date'])) ?> WIB
                                        <br><span class="text-slate-400 font-sans"><?= htmlspecialchars($exam['duration']) ?> Menit</span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-slate-700 font-medium">
                                        <?= htmlspecialchars($exam['examiner']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php if ($exam['status'] == 'Berlangsung'): ?>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Berlangsung
                                            </span>
                                        <?php elseif ($exam['status'] == 'Mendatang'): ?>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                <i class="fa-regular fa-clock text-xs"></i> Mendatang
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                <i class="fa-solid fa-check text-xs"></i> Selesai
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            <a href="update.php?id=<?= $exam['id'] ?>" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit Ujian">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <a href="delete.php?id=<?= $exam['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data ujian ini?');" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Ujian">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="bg-white border-t border-slate-200 py-4 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs text-slate-500">
            &copy; <?= date('Y') ?> Uji Unit Kompetensi (UUK) - Teknik Komputer dan Jaringan SMK
        </div>
    </footer>
</body>
</html>