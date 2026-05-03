<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$title = "Laporan Beasiswa";
$base_url = "../";
include '../config/koneksi.php';
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-graduation-cap me-2"></i>Laporan Siswa Layak Beasiswa</h3>
            <div>
                <a href="export_beasiswa_excel.php" class="btn btn-success ms-2">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
            </div>
        </div>

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status Beasiswa</label>
                        <select name="status" class="form-select">
                            <option value="">Semua</option>
                            <option value="Ya" <?= isset($_GET['status']) && $_GET['status'] == 'Ya' ? 'selected' : '' ?>>Layak</option>
                            <option value="Tidak" <?= isset($_GET['status']) && $_GET['status'] == 'Tidak' ? 'selected' : '' ?>>Tidak Layak</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Minimal Skor</label>
                        <select name="min_skor" class="form-select">
                            <option value="">Semua</option>
                            <option value="70" <?= isset($_GET['min_skor']) && $_GET['min_skor'] == '70' ? 'selected' : '' ?>>≥ 70%</option>
                            <option value="75" <?= isset($_GET['min_skor']) && $_GET['min_skor'] == '75' ? 'selected' : '' ?>>≥ 75%</option>
                            <option value="80" <?= isset($_GET['min_skor']) && $_GET['min_skor'] == '80' ? 'selected' : '' ?>>≥ 80%</option>
                            <option value="85" <?= isset($_GET['min_skor']) && $_GET['min_skor'] == '85' ? 'selected' : '' ?>>≥ 85%</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kelas</label>
                        <select name="kelas" class="form-select">
                            <option value="">Semua Kelas</option>
                            <option value="VII" <?= isset($_GET['kelas']) && $_GET['kelas'] == 'VII' ? 'selected' : '' ?>>VII</option>
                            <option value="VIII" <?= isset($_GET['kelas']) && $_GET['kelas'] == 'VIII' ? 'selected' : '' ?>>VIII</option>
                            <option value="IX" <?= isset($_GET['kelas']) && $_GET['kelas'] == 'IX' ? 'selected' : '' ?>>IX</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistik -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Siswa</h5>
                        <h2 class="mb-0">
                            <?php
                            $total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM siswa"));
                            echo $total['total'];
                            ?>
                        </h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Layak Beasiswa</h5>
                        <h2 class="mb-0">
                            <?php
                            $layak = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM siswa WHERE rekomendasi_beasiswa = 'Ya'"));
                            echo $layak['total'];
                            ?>
                        </h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h5 class="card-title">Tidak Layak</h5>
                        <h2 class="mb-0">
                            <?php
                            $tidak = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM siswa WHERE rekomendasi_beasiswa = 'Tidak'"));
                            echo $tidak['total'];
                            ?>
                        </h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Rata-rata Skor</h5>
                        <h2 class="mb-0">
                            <?php
                            $avg = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(skor_beasiswa) as avg FROM siswa WHERE skor_beasiswa > 0"));
                            echo number_format($avg['avg'] ?? 0, 1) . '%';
                            ?>
                        </h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="tabelBeasiswa">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Rata-rata Nilai</th>
                                <th>Pendidikan Ibu</th>
                                <th>Penghasilan Ayah</th>
                                <th>Skor Beasiswa</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $where = [];
                            
                            if(isset($_GET['status']) && $_GET['status'] != '') {
                                $where[] = "rekomendasi_beasiswa = '" . clean_input($_GET['status']) . "'";
                            }
                            if(isset($_GET['min_skor']) && $_GET['min_skor'] != '') {
                                $min = clean_input($_GET['min_skor']);
                                $where[] = "skor_beasiswa >= $min";
                            }
                            if(isset($_GET['kelas']) && $_GET['kelas'] != '') {
                                $where[] = "kelas = '" . clean_input($_GET['kelas']) . "'";
                            }
                            
                            $where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
                            $query = "SELECT * FROM siswa $where_sql ORDER BY skor_beasiswa DESC";
                            $result = mysqli_query($conn, $query);
                            
                            while($row = mysqli_fetch_assoc($result)):
                                $skor = $row['skor_beasiswa'] ?? 0;
                                $status = $row['rekomendasi_beasiswa'] ?? 'Tidak';
                                $badge_class = $status == 'Ya' ? 'success' : 'secondary';
                                $badge_icon = $status == 'Ya' ? '✅' : '❌';
                                
                                // Warna progress bar
                                if($skor >= 80) $progress_class = 'bg-success';
                                elseif($skor >= 50) $progress_class = 'bg-warning';
                                else $progress_class = 'bg-danger';
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= htmlspecialchars($row['nama_siswa']) ?></strong></td>
                                <td><?= $row['kelas'] ?></td>
                                <td><?= number_format($row['rata_rata'], 2) ?></td>
                                <td><?= $row['pendidikan_ibu'] ?? '-' ?></td>
                                <td><?= $row['penghasilan_ayah'] ?? '-' ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2"><?= number_format($skor, 2) ?>%</span>
                                        <div class="progress flex-grow-1" style="height: 8px; width: 100px;">
                                            <div class="progress-bar <?= $progress_class ?>" style="width: <?= $skor ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $badge_class ?>">
                                        <?= $badge_icon ?> <?= $status ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            
                            <?php if(mysqli_num_rows($result) == 0): ?>
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada data</td>
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
    $('#tabelBeasiswa').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' },
        order: [[6, 'desc']]
    });
});
</script>

<?php include '../includes/footer.php'; ?>