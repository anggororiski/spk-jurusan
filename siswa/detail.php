<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$title = "Detail Siswa";
$base_url = "../";
include '../config/koneksi.php';
include '../includes/header.php';
include '../includes/sidebar.php';

$id = clean_input($_GET['id']);
$data = mysqli_query($conn, "SELECT * FROM siswa WHERE id = '$id'");
$siswa = mysqli_fetch_assoc($data);

if(!$siswa) {
    header("Location: index.php?error=Data tidak ditemukan");
    exit;
}

// Fungsi untuk format penghasilan
function formatPenghasilan($penghasilan) {
    if(empty($penghasilan)) return '-';
    if(strpos($penghasilan, '<') !== false || strpos($penghasilan, '>') !== false) {
        $penghasilan = str_replace('.', '', $penghasilan);
        return $penghasilan;
    }
    $angkasaja = preg_replace('/[^0-9]/', '', $penghasilan);
    if(is_numeric($angkasaja) && $angkasaja > 0) {
        return 'Rp ' . number_format($angkasaja, 0, ',', '.');
    }
    return $penghasilan;
}
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-user-circle me-2"></i>Detail Siswa</h3>
            <div>
                <a href="edit.php?id=<?= $id ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <!-- Row 1: Profil dan Beasiswa -->
        <div class="row">
            <!-- Kolom Kiri: Profil -->
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Profil Siswa</h5>
                    </div>
                    <div class="card-body text-center">
                        <i class="fas fa-user-circle fa-5x text-primary mb-3"></i>
                        <h4><?= htmlspecialchars($siswa['nama_siswa']) ?></h4>
                        <p class="text-muted"><?= htmlspecialchars($siswa['jurusan_asal']) ?> | Kelas <?= $siswa['kelas'] ?></p>
                        <hr>
                        <table class="table table-sm table-borderless text-start">
                            <tr>
                                <td width="40%"><strong>Jenis Kelamin</strong></td>
                                <td><?= $siswa['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Pendidikan Ayah</strong></td>
                                <td><?= htmlspecialchars($siswa['pendidikan_ayah'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Pendidikan Ibu</strong></td>
                                <td><?= htmlspecialchars($siswa['pendidikan_ibu'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Penghasilan Ayah</strong></td>
                                <td><?= formatPenghasilan($siswa['penghasilan_ayah']) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Beasiswa -->
            <div class="col-md-8 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Informasi Beasiswa</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Skor Beasiswa</h6>
                                <h2 class="<?= ($siswa['skor_beasiswa'] ?? 0) >= 65 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format($siswa['skor_beasiswa'] ?? 0, 2) ?>%
                                </h2>
                                <div class="progress mt-2 mb-2">
                                    <div class="progress-bar <?= ($siswa['skor_beasiswa'] ?? 0) >= 65 ? 'bg-success' : 'bg-danger' ?>" 
                                         style="width: <?= min($siswa['skor_beasiswa'] ?? 0, 100) ?>%">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Status</h6>
                                <?php if(($siswa['rekomendasi_beasiswa'] ?? 'Tidak') == 'Ya'): ?>
                                    <span class="badge bg-success fs-5 px-3 py-2">✅ LAYAK BEASISWA</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary fs-5 px-3 py-2">❌ TIDAK LAYAK</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <hr>
                        <h6 class="mb-3"><i class="fas fa-chart-line me-2"></i>Kriteria Penilaian Beasiswa</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Pendidikan Ibu</span>
                                    <strong><?= htmlspecialchars($siswa['pendidikan_ibu'] ?? '-') ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Penghasilan Ayah</span>
                                    <strong><?= formatPenghasilan($siswa['penghasilan_ayah']) ?></strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Rata-rata Nilai</span>
                                    <strong><?= round($siswa['rata_rata'], 2) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Sikap</span>
                                    <strong><?= $siswa['sikap'] ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Hasil SPK -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Hasil Analisis SPK</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="text-primary">Skor DKV</h6>
                                        <h2 class="text-primary"><?= round(($siswa['skor_dkv'] ?? 0) * 100, 2) ?>%</h2>
                                        <div class="progress mt-2">
                                            <div class="progress-bar bg-primary" style="width: <?= min(($siswa['skor_dkv'] ?? 0) * 100, 100) ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="text-danger">Skor TKR</h6>
                                        <h2 class="text-danger"><?= round(($siswa['skor_tkr'] ?? 0) * 100, 2) ?>%</h2>
                                        <div class="progress mt-2">
                                            <div class="progress-bar bg-danger" style="width: <?= min(($siswa['skor_tkr'] ?? 0) * 100, 100) ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-<?= $siswa['rekomendasi_jurusan'] == 'DKV' ? 'primary' : 'danger' ?> text-center mt-3">
                            <h5 class="mb-1">
                                <i class="fas fa-<?= $siswa['rekomendasi_jurusan'] == 'DKV' ? 'palette' : 'wrench' ?> me-2"></i>
                                Rekomendasi Jurusan: <strong><?= $siswa['rekomendasi_jurusan'] ?? 'Belum dihitung' ?></strong>
                            </h5>
                            <p class="mb-0">
                                Tingkat Kecocokan: 
                                <span class="badge bg-<?= $siswa['tingkat_kecocokan'] == 'Sangat Cocok' ? 'success' : ($siswa['tingkat_kecocokan'] == 'Cocok' ? 'primary' : 'warning') ?>">
                                    <?= $siswa['tingkat_kecocokan'] ?? 'Belum dihitung' ?>
                                </span>
                            </p>
                        </div>
                        
                        <?php if(!$siswa['rekomendasi_jurusan']): ?>
                            <div class="text-center mt-3">
                                <a href="../saw/index.php" class="btn btn-primary">
                                    <i class="fas fa-calculator"></i> Hitung SPK Sekarang
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 3: Nilai Akademik dan Perilaku -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-book me-2"></i>Nilai Akademik</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr class="table-light">
                                <th width="50%">NUS Matematika</th>
                                <td class="text-center"><strong><?= $siswa['nus_mtk_smp'] ?></strong></td>
                            </tr>
                            <tr>
                                <th>NUS Bahasa Indonesia</th>
                                <td class="text-center"><strong><?= $siswa['nus_bind_smp'] ?></strong></td>
                            </tr>
                            <tr class="table-light">
                                <th>NUS Bahasa Inggris</th>
                                <td class="text-center"><strong><?= $siswa['nus_bing_smp'] ?></strong></td>
                            </tr>
                            <tr>
                                <th>NUS Rata-rata</th>
                                <td class="text-center"><strong><?= round($siswa['rata_rata'], 2) ?></strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-heart me-2"></i>Nilai Perilaku</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr class="table-light">
                                <th width="50%">Disiplin</th>
                                <td class="text-center">
                                    <strong><?= $siswa['disiplin'] ?></strong>
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar bg-success" style="width: <?= $siswa['disiplin'] ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggung Jawab</th>
                                <td class="text-center">
                                    <strong><?= $siswa['tanggung_jawab'] ?></strong>
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar bg-info" style="width: <?= $siswa['tanggung_jawab'] ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="table-light">
                                <th>Sikap</th>
                                <td class="text-center">
                                    <strong><?= $siswa['sikap'] ?></strong>
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar bg-warning" style="width: <?= $siswa['sikap'] ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>Komunikasi</th>
                                <td class="text-center">
                                    <strong><?= $siswa['komunikasi'] ?></strong>
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar bg-danger" style="width: <?= $siswa['komunikasi'] ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Responsive adjustments */
@media (max-width: 768px) {
    .card-header h5 {
        font-size: 0.9rem;
    }
    .table td, .table th {
        padding: 0.5rem;
        font-size: 0.85rem;
    }
    h2 {
        font-size: 1.5rem;
    }
}
</style>

<?php include '../includes/footer.php'; ?>