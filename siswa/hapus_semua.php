<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

include '../config/koneksi.php';

// Konfirmasi keamanan dengan parameter
if(!isset($_GET['confirm']) || $_GET['confirm'] != 'yes') {
    header("Location: index.php?error=Konfirmasi tidak valid");
    exit;
}

// Hapus semua data dari tabel siswa
$query1 = "DELETE FROM siswa";
$query2 = "DELETE FROM riwayat";
$query3 = "ALTER TABLE siswa AUTO_INCREMENT = 1";

$success = 0;
$errors = [];

if(mysqli_query($conn, $query1)) {
    $success++;
    $jumlah = mysqli_affected_rows($conn);
} else {
    $errors[] = "Gagal menghapus data siswa: " . mysqli_error($conn);
}

if(mysqli_query($conn, $query2)) {
    $success++;
} else {
    $errors[] = "Gagal menghapus riwayat: " . mysqli_error($conn);
}

if(mysqli_query($conn, $query3)) {
    $success++;
} else {
    $errors[] = "Gagal mereset auto increment: " . mysqli_error($conn);
}

if($success >= 2) {
    $_SESSION['success'] = "Berhasil menghapus semua data siswa! ($jumlah data terhapus)";
} else {
    $_SESSION['error'] = "Gagal menghapus data: " . implode(', ', $errors);
}

header("Location: index.php");
exit;
?>