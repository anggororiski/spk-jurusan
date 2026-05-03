<?php
// Deteksi halaman saat ini
$current_file = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Tentukan base URL berdasarkan lokasi file
if($current_dir == 'includes') {
    $base_url = '../';
} elseif($current_dir == 'siswa' || $current_dir == 'saw' || $current_dir == 'riwayat') {
    $base_url = '../';
} else {
    $base_url = '';
}

// Tentukan menu aktif (HANYA SATU YANG AKTIF)
$active_dashboard = ($current_file == 'dashboard.php');
$active_siswa = ($current_dir == 'siswa' && $current_file == 'index.php');
$active_saw = ($current_dir == 'saw' && $current_file == 'index.php');
$active_bobot = ($current_file == 'bobot.php');
$active_riwayat = ($current_dir == 'riwayat' && $current_file == 'index.php');
$active_laporan_beasiswa = ($current_file == 'laporan_beasiswa.php');
?>

<div class="sidebar" id="mainSidebar">
    <div class="user-info">
        <i class="fas fa-user-circle"></i>
        <h6 class="mb-0"><?= $_SESSION['nama'] ?? 'Administrator' ?></h6>
        <h3>Spk Jurusan SMK</h3>
    </div>
    
    <div class="menu-title">MAIN MENU</div>
    <a href="<?= $base_url ?>dashboard.php" class="<?= $active_dashboard ? 'active' : '' ?>">
        <i class="fas fa-home me-2"></i> Dashboard
    </a>
    <a href="<?= $base_url ?>siswa/index.php" class="<?= $active_siswa ? 'active' : '' ?>">
        <i class="fas fa-users me-2"></i> Data Siswa
    </a>
    
    <div class="menu-title mt-3">SPK & ANALISIS</div>
    <a href="<?= $base_url ?>saw/index.php" class="<?= $active_saw ? 'active' : '' ?>">
        <i class="fas fa-calculator me-2"></i> Hitung SPK
    </a>
    <a href="<?= $base_url ?>saw/bobot.php" class="<?= $active_bobot ? 'active' : '' ?>">
        <i class="fas fa-balance-scale me-2"></i> Bobot Kriteria
    </a>
    
    <div class="menu-title mt-3">LAPORAN</div>
    <a href="<?= $base_url ?>riwayat/index.php" class="<?= $active_riwayat ? 'active' : '' ?>">
        <i class="fas fa-history me-2"></i> Riwayat
    </a>
    <a href="<?= $base_url ?>siswa/laporan_beasiswa.php" class="<?= $active_laporan_beasiswa ? 'active' : '' ?>">
        <i class="fas fa-graduation-cap me-2"></i> Laporan Beasiswa
    </a>
    
    <div class="menu-title mt-3">PENGATURAN</div>
    <a href="#" data-bs-toggle="modal" data-bs-target="#modalHapusSemuaSidebar">
        <i class="fas fa-trash-alt me-2"></i> Hapus Semua Data
    </a>
    
    <hr>
    <a href="<?= $base_url ?>logout.php">
        <i class="fas fa-sign-out-alt me-2"></i> Logout
    </a>
</div>

<!-- Modal Hapus Semua di Sidebar -->
<div class="modal fade" id="modalHapusSemuaSidebar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Hapus Semua Data</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus <strong>SEMUA data siswa</strong>?</p>
                <p class="text-danger"><i class="fas fa-warning"></i> Tindakan ini tidak dapat dibatalkan!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="<?= $base_url ?>siswa/hapus_semua.php?confirm=yes" class="btn btn-danger">
                    <i class="fas fa-trash-alt"></i> Ya, Hapus Semua
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Tombol Toggle Sidebar (untuk mobile) di KIRI BAWAH -->
<button class="btn btn-primary toggle-sidebar-btn" id="toggleSidebarBtn">
    <i class="fas fa-bars"></i>
</button>

<!-- Overlay untuk menutup sidebar saat diklik di luar -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>