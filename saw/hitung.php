<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}
$title = "Hasil SPK";
$base_url = "../";
include '../config/koneksi.php';
include '../includes/header.php';
include '../includes/sidebar.php';

// Fungsi hitung skor
function hitungSkor($siswa, $jurusan, $conn) {
    $query = "SELECT kriteria, bobot, tipe FROM bobot_kriteria WHERE jurusan = '$jurusan'";
    $result = mysqli_query($conn, $query);
    
    $bobot = [];
    $tipe = [];
    while($row = mysqli_fetch_assoc($result)) {
        $bobot[$row['kriteria']] = $row['bobot'];
        $tipe[$row['kriteria']] = $row['tipe'];
    }
    
    $max_values = [];
    foreach(array_keys($bobot) as $kriteria) {
        if($kriteria == 'PENGHASILAN_AYAH') {
            $q = mysqli_query($conn, "SELECT MAX(penghasilan_ayah) as max_val FROM siswa");
        } else {
            $k_db = strtolower($kriteria);
            $q = mysqli_query($conn, "SELECT MAX($k_db) as max_val FROM siswa");
        }
        $max_values[$kriteria] = mysqli_fetch_assoc($q)['max_val'] ?? 1;
    }
    
    $total = 0;
    foreach($bobot as $kriteria => $bobot_val) {
        if($kriteria == 'PENGHASILAN_AYAH') {
            $nilai = $siswa['penghasilan_ayah'] ?? 0;
        } else {
            $k_db = strtolower($kriteria);
            $nilai = $siswa[$k_db] ?? 0;
        }
        
        if($tipe[$kriteria] == 'benefit') {
            $norm = $max_values[$kriteria] > 0 ? $nilai / $max_values[$kriteria] : 0;
        } else {
            $norm = $nilai > 0 ? $max_values[$kriteria] / $nilai : 0;
            $norm = min($norm, 1);
        }
        $total += $norm * $bobot_val;
    }
    return $total;
}

// Proses semua siswa
$siswa_list = mysqli_query($conn, "SELECT * FROM siswa ORDER BY nama_siswa ASC");
$hasil = [];
$total_dkv = 0;
$total_tkr = 0;

while($siswa = mysqli_fetch_assoc($siswa_list)) {
    $skor_dkv = hitungSkor($siswa, 'DKV', $conn);
    $skor_tkr = hitungSkor($siswa, 'TKR', $conn);
    
    if($skor_dkv >= $skor_tkr) {
        $jurusan = 'DKV';
        $skor = $skor_dkv;
        $total_dkv++;
    } else {
        $jurusan = 'TKR';
        $skor = $skor_tkr;
        $total_tkr++;
    }
    
    if($skor >= 0.85) $kecocokan = 'Sangat Cocok';
    elseif($skor >= 0.70) $kecocokan = 'Cocok';
    elseif($skor >= 0.55) $kecocokan = 'Cukup Cocok';
    else $kecocokan = 'Kurang Cocok';
    
    // Update database
    mysqli_query($conn, "UPDATE siswa SET 
        rekomendasi_jurusan = '$jurusan',
        skor_dkv = '$skor_dkv',
        skor_tkr = '$skor_tkr',
        tingkat_kecocokan = '$kecocokan'
        WHERE id = {$siswa['id']}");
    
    $hasil[] = [
        'id' => $siswa['id'],
        'nama' => $siswa['nama_siswa'],
        'skor_dkv' => $skor_dkv,
        'skor_tkr' => $skor_tkr,
        'jurusan' => $jurusan,
        'kecocokan' => $kecocokan
    ];
}

usort($hasil, function($a, $b) {
    return max($b['skor_dkv'], $b['skor_tkr']) <=> max($a['skor_dkv'], $a['skor_tkr']);
});
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-calculator me-2"></i>Hasil Perhitungan SPK</h3>
            <div>
                <a href="export.php" class="btn btn-success me-2">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
                <button onclick="window.print()" class="btn btn-secondary">
                    <i class="fas fa-print"></i> Cetak
                </button>
            </div>
        </div>
        
        <!-- Statistik -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h5>Rekomendasi DKV</h5>
                        <h2><?= $total_dkv ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h5>Rekomendasi TKR</h5>
                        <h2><?= $total_tkr ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h5>Total Siswa</h5>
                        <h2><?= count($hasil) ?></h2>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabel Hasil -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Ranking Siswa</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="tabelHasil">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Nama Siswa</th>
                                <th>Skor DKV</th>
                                <th>Skor TKR</th>
                                <th>Rekomendasi</th>
                                <th>Tingkat Kecocokan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $rank = 1; foreach($hasil as $h): ?>
                            <tr>
                                <td>#<?= $rank++ ?></td>
                                <td><strong><?= htmlspecialchars($h['nama']) ?></strong></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span><?= round($h['skor_dkv'] * 100, 1) ?>%</span>
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-primary" style="width: <?= $h['skor_dkv'] * 100 ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span><?= round($h['skor_tkr'] * 100, 1) ?>%</span>
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-danger" style="width: <?= $h['skor_tkr'] * 100 ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $h['jurusan'] == 'DKV' ? 'primary' : 'danger' ?>">
                                        <?= $h['jurusan'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $h['kecocokan'] == 'Sangat Cocok' ? 'success' : ($h['kecocokan'] == 'Cocok' ? 'primary' : 'warning') ?>">
                                        <?= $h['kecocokan'] ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#tabelHasil').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' },
        order: [[2, 'desc']]
    });
});
</script>

<?php include '../includes/footer.php'; ?>