<?php
// ==============================================================================
// FILE: delete.php
// KETERANGAN: Eksekusi Penghapusan Data Ujian UUK berdasarkan ID (DELETE)
// ==============================================================================
session_start();
require_once 'konek_database.php';

// Cek hak akses Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $query = "DELETE FROM exams WHERE id = $id";
    mysqli_query($koneksi, $query);
}

header("Location: index.php");
exit();
?>