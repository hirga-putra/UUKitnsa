<?php
// ==============================================================================
// FILE: delete.php
// KETERANGAN: Eksekusi Penghapusan Data Ujian UUK berdasarkan ID (DELETE)
// ==============================================================================
require_once 'konek_database.php';

// Memastikan parameter ID tersedia
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Query DELETE
    $query = "DELETE FROM exams WHERE id = $id";

    if (mysqli_query($koneksi, $query)) {
        // Berhasil dihapus, arahkan kembali ke index
        header("Location: index.php?status=success_delete");
        exit();
    } else {
        // Gagal menghapus
        header("Location: index.php?status=error");
        exit();
    }
} else {
    // Jika tidak ada ID, redirect langsung ke index
    header("Location: index.php");
    exit();
}
?>