<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$title = "Bobot Kriteria";
$base_url = "../";
include '../config/koneksi.php';
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container">
        <h3 class="mb-4"><i class="fas fa-balance-scale me-2"></i>Bobot Kriteria Per Jurusan</h3>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary">
                        <h5 class="mb-0"><i class="fas fa-palette me-2"></i>DKV - Desain Komunikasi Visual</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="table-light">
                                    <tr><th>Kriteria</th><th>Bobot</th><th>Tipe</th></tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $dkv = mysqli_query($conn, "SELECT * FROM bobot_kriteria WHERE jurusan='DKV' ORDER BY bobot DESC");
                                    while($row = mysqli_fetch_assoc($dkv)):
                                    ?>
                                    <tr>
                                        <td><?= str_replace('_', ' ', $row['kriteria']) ?></td>
                                        <td><strong><?= $row['bobot'] * 100 ?>%</strong></td>
                                        <td><span class="badge bg-<?= $row['tipe'] == 'benefit' ? 'success' : 'warning' ?>"><?= $row['tipe'] == 'benefit' ? 'Benefit' : 'Cost' ?></span></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger">
                        <h5 class="mb-0"><i class="fas fa-wrench me-2"></i>TKR - Teknik Kendaraan Ringan</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="table-light">
                                    <tr><th>Kriteria</th><th>Bobot</th><th>Tipe</th></tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $tkr = mysqli_query($conn, "SELECT * FROM bobot_kriteria WHERE jurusan='TKR' ORDER BY bobot DESC");
                                    while($row = mysqli_fetch_assoc($tkr)):
                                    ?>
                                    <tr>
                                        <td><?= str_replace('_', ' ', $row['kriteria']) ?></td>
                                        <td><strong><?= $row['bobot'] * 100 ?>%</strong></td>
                                        <td><span class="badge bg-<?= $row['tipe'] == 'benefit' ? 'success' : 'warning' ?>"><?= $row['tipe'] == 'benefit' ? 'Benefit' : 'Cost' ?></span></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Keterangan</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Benefit (Semakin Tinggi Semakin Baik):</h6>
                        <ul>
                            <li>Semua nilai akademik (MTK, B.Indo, B.Ing, Rata-rata)</li>
                            <li>Nilai perilaku (Disiplin, Tanggung Jawab, Sikap, Komunikasi)</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Cost (Semakin Rendah Semakin Baik):</h6>
                        <ul>
                            <li>Penghasilan orang tua (semakin rendah lebih membutuhkan beasiswa)</li>
                            <li>Standar deviasi (semakin rendah nilai lebih konsisten)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>