<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

include '../config/koneksi.php';

$id = clean_input($_GET['id']);

$query = "DELETE FROM siswa WHERE id = '$id'";
if(mysqli_query($conn, $query)) {
    header("Location: index.php?success=Data berhasil dihapus");
} else {
    header("Location: index.php?error=Gagal menghapus data");
}
exit;
?>