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

// Variabel untuk menyimpan hasil
$hasil = [];
$total_dkv = 0;
$total_tkr = 0;
$proses = false;
$pesan = '';

// Fungsi hitung skor
function hitungSkor($siswa, $jurusan, $conn) {
    $query = "SELECT kriteria, bobot, tipe FROM bobot_kriteria WHERE jurusan = '$jurusan'";
    $result = mysqli_query($conn, $query);
    
    $bobot = [];
    $tipe = [];
    while($row = mysqli_fetch_assoc($result)) {
        $bobot[$row['kriteria']] = (float)$row['bobot'];
        $tipe[$row['kriteria']] = $row['tipe'];
    }
    
    if(empty($bobot)) {
        return 0;
    }
    
    $total = 0;
    foreach($bobot as $kriteria => $bobot_val) {
        if($kriteria == 'PENGHASILAN_AYAH') {
            $nilai = $siswa['penghasilan_ayah'] ?? 0;
            if(is_string($nilai)) {
                // Hapus titik dan konversi ke angka
                $nilai = (float)str_replace('.', '', $nilai);
                // Jika masih ada karakter non angka, anggap 0
                if(!is_numeric($nilai)) $nilai = 0;
            } else {
                $nilai = (float)$nilai;
            }
            $max_ideal = 10000000;
            if($tipe[$kriteria] == 'benefit') {
                $norm = min(1, $nilai / $max_ideal);
            } else {
                $norm = min(1, $max_ideal / max($nilai, 1));
            }
        } else {
            $k_db = strtolower($kriteria);
            $nilai = isset($siswa[$k_db]) ? (float)$siswa[$k_db] : 0;
            $max_ideal = 100;
            if($tipe[$kriteria] == 'benefit') {
                $norm = min(1, $nilai / $max_ideal);
            } else {
                $norm = min(1, $max_ideal / max($nilai, 1));
            }
        }
        
        if(is_nan($norm)) $norm = 0;
        $total += $norm * $bobot_val;
    }
    
    return min($total, 1);
}

// Cek apakah tombol proses ditekan
if(isset($_POST['proses_hitung'])) {
    $proses = true;
    
    // Proses semua siswa
    $siswa_list = mysqli_query($conn, "SELECT * FROM siswa ORDER BY nama_siswa ASC");
    
    if(mysqli_num_rows($siswa_list) == 0) {
        $pesan = "Belum ada data siswa. Silakan tambah data siswa terlebih dahulu.";
    } else {
        while($siswa = mysqli_fetch_assoc($siswa_list)) {
            $skor_dkv = hitungSkor($siswa, 'DKV', $conn);
            $skor_tkr = hitungSkor($siswa, 'TKR', $conn);
            
            if(is_nan($skor_dkv)) $skor_dkv = 0;
            if(is_nan($skor_tkr)) $skor_tkr = 0;
            
            $persen_dkv = round($skor_dkv * 100, 2);
            $persen_tkr = round($skor_tkr * 100, 2);
            
            // Ambil skor beasiswa dari database
            $skor_beasiswa = isset($siswa['skor_beasiswa']) ? (float)$siswa['skor_beasiswa'] : 0;
            $rekomendasi_beasiswa = isset($siswa['rekomendasi_beasiswa']) ? $siswa['rekomendasi_beasiswa'] : 'Tidak';
            
            if($skor_dkv >= $skor_tkr) {
                $jurusan_terbaik = 'DKV';
                $skor_terbaik = $skor_dkv;
                $total_dkv++;
            } else {
                $jurusan_terbaik = 'TKR';
                $skor_terbaik = $skor_tkr;
                $total_tkr++;
            }
            
            if($skor_terbaik >= 0.85) $kecocokan = 'Sangat Cocok';
            elseif($skor_terbaik >= 0.70) $kecocokan = 'Cocok';
            elseif($skor_terbaik >= 0.55) $kecocokan = 'Cukup Cocok';
            else $kecocokan = 'Kurang Cocok';
            
            $skor_dkv_db = round($skor_dkv, 4);
            $skor_tkr_db = round($skor_tkr, 4);
            
            mysqli_query($conn, "UPDATE siswa SET 
                rekomendasi_jurusan = '$jurusan_terbaik',
                skor_dkv = '$skor_dkv_db',
                skor_tkr = '$skor_tkr_db',
                tingkat_kecocokan = '$kecocokan'
                WHERE id = {$siswa['id']}");
            
            $hasil[] = [
                'nama' => $siswa['nama_siswa'],
                'skor_dkv' => $skor_dkv,
                'skor_tkr' => $skor_tkr,
                'persen_dkv' => $persen_dkv,
                'persen_tkr' => $persen_tkr,
                'jurusan' => $jurusan_terbaik,
                'kecocokan' => $kecocokan,
                'skor_beasiswa' => $skor_beasiswa,
                'status_beasiswa' => $rekomendasi_beasiswa
            ];
        }
        
        // Sorting berdasarkan skor tertinggi
        usort($hasil, function($a, $b) {
            return max($b['skor_dkv'], $b['skor_tkr']) <=> max($a['skor_dkv'], $a['skor_tkr']);
        });
        
        // ========== SIMPAN KE RIWAYAT (HANYA SAAT PROSES) ==========
        $data_riwayat = [];
        foreach($hasil as $h) {
            $data_riwayat[] = [
                'nama' => $h['nama'],
                'skor_dkv' => $h['persen_dkv'],
                'skor_tkr' => $h['persen_tkr'],
                'rekomendasi' => $h['jurusan'],
                'kecocokan' => $h['kecocokan'],
                'skor_beasiswa' => $h['skor_beasiswa'],
                'status_beasiswa' => $h['status_beasiswa']
            ];
        }
        
        $json_data = mysqli_real_escape_string($conn, json_encode($data_riwayat));
        $total_siswa = count($hasil);
        $waktu = date('Y-m-d H:i:s');
        
        // Cek apakah tabel riwayat ada
        $cek_tabel = mysqli_query($conn, "SHOW TABLES LIKE 'riwayat'");
        if(mysqli_num_rows($cek_tabel) == 0) {
            mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `riwayat` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `waktu` DATETIME NOT NULL,
                `total_siswa` INT(11) NOT NULL,
                `data` TEXT NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
        
        $query_simpan = "INSERT INTO riwayat (waktu, total_siswa, data) VALUES ('$waktu', '$total_siswa', '$json_data')";
        if(mysqli_query($conn, $query_simpan)) {
            $pesan = "Perhitungan berhasil! Riwayat telah disimpan.";
        } else {
            $pesan = "Perhitungan berhasil, tetapi gagal menyimpan riwayat.";
        }
    }
}

// Ambil data siswa untuk ditampilkan (jika sudah ada)
$siswa_list_tampil = mysqli_query($conn, "SELECT * FROM siswa ORDER BY nama_siswa ASC");
$hasil_tampil = [];
$total_dkv_tampil = 0;
$total_tkr_tampil = 0;

while($siswa = mysqli_fetch_assoc($siswa_list_tampil)) {
    $skor_dkv = isset($siswa['skor_dkv']) ? (float)$siswa['skor_dkv'] : 0;
    $skor_tkr = isset($siswa['skor_tkr']) ? (float)$siswa['skor_tkr'] : 0;
    $jurusan = $siswa['rekomendasi_jurusan'] ?? 'Belum';
    $kecocokan = $siswa['tingkat_kecocokan'] ?? 'Belum';
    
    if($jurusan == 'DKV') $total_dkv_tampil++;
    elseif($jurusan == 'TKR') $total_tkr_tampil++;
    
    $hasil_tampil[] = [
        'nama' => $siswa['nama_siswa'],
        'persen_dkv' => round($skor_dkv * 100, 2),
        'persen_tkr' => round($skor_tkr * 100, 2),
        'jurusan' => $jurusan,
        'kecocokan' => $kecocokan
    ];
}
?>

<div class="main-content">
    <div class="container-fluid">
        <h3 class="mb-4"><i class="fas fa-calculator me-2"></i>Hasil Perhitungan SPK</h3>
        
        <?php if($pesan): ?>
            <div class="alert alert-<?= strpos($pesan, 'berhasil') !== false ? 'success' : 'warning' ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?= strpos($pesan, 'berhasil') !== false ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
                <?= $pesan ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Tombol Proses Hitung -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <p class="mb-0 text-muted">
                                <i class="fas fa-info-circle me-2"></i>
                                Klik tombol di samping untuk melakukan perhitungan SPK dan menyimpan ke riwayat.
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="submit" name="proses_hitung" class="btn btn-primary btn-lg">
                                <i class="fas fa-play me-2"></i> Proses Hitung SPK
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Statistik -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h5>Rekomendasi DKV</h5>
                        <h2><?= $total_dkv_tampil ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h5>Rekomendasi TKR</h5>
                        <h2><?= $total_tkr_tampil ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h5>Total Siswa</h5>
                        <h2><?= count($hasil_tampil) ?></h2>
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
                                <th>Kecocokan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($hasil_tampil) > 0): 
                                $rank = 1; 
                                usort($hasil_tampil, function($a, $b) {
                                    return max($b['persen_dkv'], $b['persen_tkr']) <=> max($a['persen_dkv'], $a['persen_tkr']);
                                });
                                foreach($hasil_tampil as $h): 
                                    $warna_jurusan = $h['jurusan'] == 'DKV' ? 'primary' : ($h['jurusan'] == 'TKR' ? 'danger' : 'secondary');
                                    $warna_kecocokan = $h['kecocokan'] == 'Sangat Cocok' ? 'success' : ($h['kecocokan'] == 'Cocok' ? 'primary' : ($h['kecocokan'] == 'Cukup Cocok' ? 'warning' : 'secondary'));
                            ?>
                            <tr>
                                <td>#<?= $rank++ ?></td>
                                <td><strong><?= htmlspecialchars($h['nama']) ?></strong></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span><?= $h['persen_dkv'] ?>%</span>
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-primary" style="width: <?= min($h['persen_dkv'], 100) ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span><?= $h['persen_tkr'] ?>%</span>
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-danger" style="width: <?= min($h['persen_tkr'], 100) ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if($h['jurusan'] != 'Belum'): ?>
                                        <span class="badge bg-<?= $warna_jurusan ?>"><?= $h['jurusan'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Belum</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($h['kecocokan'] != 'Belum'): ?>
                                        <span class="badge bg-<?= $warna_kecocokan ?>"><?= $h['kecocokan'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; 
                            else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Belum ada data siswa. Silakan tambah siswa terlebih dahulu.会
                            </tr>
                            <?php endif; ?>
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
        responsive: true,
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' },
        order: [[2, 'desc']]
    });
    
    // Auto hilangkan alert setelah 3 detik
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 3000);
});
</script>

<?php include '../includes/footer.php'; ?>