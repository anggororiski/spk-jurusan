<?php
// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login untuk semua halaman kecuali login
$current_page = basename($_SERVER['PHP_SELF']);
if ($current_page != 'login.php' && !isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Tentukan base URL
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
if($current_dir == 'includes') {
    $base_url = '../';
} elseif($current_dir == 'siswa' || $current_dir == 'saw' || $current_dir == 'riwayat') {
    $base_url = '../';
} else {
    $base_url = '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title><?= $title ?? 'SPK Pemilihan Jurusan SMK' ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <!-- DataTables Responsive -->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
    
    <!-- ========== CUSTOM CSS ========== -->
    <!-- CSS Global -->
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/style.css">
    <!-- CSS Khusus Header, Sidebar, Footer -->
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/includes.css">

    <!-- Flatpickr CSS & JS (Kalender lengkap) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    
</head>
<body>