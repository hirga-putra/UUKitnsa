<?php
// ==============================================================================
// FILE: update.php
// KETERANGAN: Form dan Pengolahan Sunting Data Ujian UUK (UPDATE)
// ==============================================================================
session_start();
require_once 'konek_database.php';

// Cek hak akses Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = (int)$_GET['id'];
$query_fetch = "SELECT * FROM exams WHERE id = $id";
$result_fetch = mysqli_query($koneksi, $query_fetch);
$data = mysqli_fetch_assoc($result_fetch);

if (!$data) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = mysqli_real_escape_string($koneksi, trim($_POST['title']));
    $target_class = mysqli_real_escape_string($koneksi, trim($_POST['target_class']));
    $duration     = (int)$_POST['duration'];
    $exam_date    = mysqli_real_escape_string($koneksi, $_POST['exam_date']);
    $status       = mysqli_real_escape_string($koneksi, $_POST['status']);
    $examiner     = mysqli_real_escape_string($koneksi, trim($_POST['examiner']));
    $description  = mysqli_real_escape_string($koneksi, trim($_POST['description']));

    $query_update = "UPDATE exams SET 
                        title = '$title',
                        target_class = '$target_class',
                        duration = $duration,
                        exam_date = '$exam_date',
                        status = '$status',
                        examiner = '$examiner',
                        description = '$description'
                     WHERE id = $id";

    if (mysqli_query($koneksi, $query_update)) {
        header("Location: index.php");
        exit();
    } else {
        $error_msg = mysqli_error($koneksi);
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Ujian UUK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="h-full flex flex-col font-['Inter'] text-slate-700 bg-slate-50 antialiased">
    <header class="bg-white border-b border-slate-200 py-4">
        <div class="max-w-3xl mx-auto px-4 flex justify-between items-center">
            <h1 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-indigo-600"></i> Edit Data Ujian UUK
            </h1>
            <a href="index.php" class="text-xs font-semibold text-slate-500 hover:text-slate-800 flex items-center gap-1">
                <i class="fa-solid fa-arrow-left"></i> Batal & Kembali
            </a>
        </div>
    </header>

    <main class="flex-1 max-w-3xl w-full mx-auto px-4 py-8">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden">
            <div class="bg-slate-900 px-6 py-4 text-white">
                <h2 class="font-bold text-base">Edit Modul UUK (ID: <?= $data['id'] ?>)</h2>
            </div>
            <form action="update.php?id=<?= $data['id'] ?>" method="POST" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Judul / Modul Ujian</label>
                    <input type="text" name="title" required value="<?= htmlspecialchars($data['title']) ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Target Kelas</label>
                        <select name="target_class" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm bg-white">
                            <?php 
                            $classes = ["Semua XI TKJ", "XI TKJ 1", "XI TKJ 2", "XI TKJ 3", "XI TKJ 4"];
                            foreach ($classes as $c) {
                                $selected = ($data['target_class'] == $c) ? 'selected' : '';
                                echo "<option value='$c' $selected>$c</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Durasi Alokasi (Menit)</label>
                        <input type="number" name="duration" required min="30" max="360" value="<?= $data['duration'] ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Tanggal & Waktu</label>
                        <input type="datetime-local" name="exam_date" required value="<?= date('Y-m-d\TH:i', strtotime($data['exam_date'])) ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Status Pelaksanaan</label>
                        <select name="status" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm bg-white">
                            <?php 
                            $statuses = ["Mendatang", "Berlangsung", "Selesai"];
                            foreach ($statuses as $st) {
                                $selected = ($data['status'] == $st) ? 'selected' : '';
                                echo "<option value='$st' $selected>$st</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Nama Penguji / Asesor</label>
                    <input type="text" name="examiner" required value="<?= htmlspecialchars($data['examiner']) ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Deskripsi Ringkas Materi</label>
                    <textarea name="description" rows="4" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm"><?= htmlspecialchars($data['description']) ?></textarea>
                </div>
                <div class="pt-4 flex justify-end space-x-3 border-t border-slate-100">
                    <a href="index.php" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl">Batal</a>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl text-sm shadow-md">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>