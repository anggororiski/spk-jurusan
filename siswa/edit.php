<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$title = "Edit Siswa";
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
?>

<div class="main-content">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-edit me-2"></i>Edit Data Siswa</h3>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        
        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="update.php" id="formSiswa">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Siswa *</label>
                            <input type="text" class="form-control" name="nama_siswa" value="<?= htmlspecialchars($siswa['nama_siswa']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Asal Sekolah *</label>
                            <input type="text" class="form-control" name="jurusan_asal" value="<?= htmlspecialchars($siswa['jurusan_asal']) ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kelas *</label>
                            <select class="form-select" name="kelas" required>
                                <option value="VII" <?= $siswa['kelas'] == 'VII' ? 'selected' : '' ?>>VII</option>
                                <option value="VIII" <?= $siswa['kelas'] == 'VIII' ? 'selected' : '' ?>>VIII</option>
                                <option value="IX" <?= $siswa['kelas'] == 'IX' ? 'selected' : '' ?>>IX</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jenis Kelamin *</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="jenis_kelamin" value="L" <?= $siswa['jenis_kelamin'] == 'L' ? 'checked' : '' ?> required>
                                    <label class="form-check-label">Laki-laki</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="jenis_kelamin" value="P" <?= $siswa['jenis_kelamin'] == 'P' ? 'checked' : '' ?> required>
                                    <label class="form-check-label">Perempuan</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Penghasilan Ayah (Rp) *</label>
                            <input type="number" class="form-control" name="penghasilan_ayah" value="<?= $siswa['penghasilan_ayah'] ?>" required>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <hr>
                            <h5><i class="fas fa-book me-2"></i>Nilai Akademik</h5>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">NUS Matematika *</label>
                            <input type="number" class="form-control nilai" name="nus_mtk_smp" value="<?= $siswa['nus_mtk_smp'] ?>" required min="0" max="100" oninput="hitungOtomatis()">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">NUS Bahasa Indonesia *</label>
                            <input type="number" class="form-control nilai" name="nus_bind_smp" value="<?= $siswa['nus_bind_smp'] ?>" required min="0" max="100" oninput="hitungOtomatis()">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">NUS Bahasa Inggris *</label>
                            <input type="number" class="form-control nilai" name="nus_bing_smp" value="<?= $siswa['nus_bing_smp'] ?>" required min="0" max="100" oninput="hitungOtomatis()">
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <hr>
                            <h5><i class="fas fa-heart me-2"></i>Nilai Perilaku</h5>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Disiplin *</label>
                            <input type="number" class="form-control nilai" name="disiplin" value="<?= $siswa['disiplin'] ?>" required min="0" max="100" oninput="hitungOtomatis()">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tanggung Jawab *</label>
                            <input type="number" class="form-control nilai" name="tanggung_jawab" value="<?= $siswa['tanggung_jawab'] ?>" required min="0" max="100" oninput="hitungOtomatis()">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Sikap *</label>
                            <input type="number" class="form-control nilai" name="sikap" value="<?= $siswa['sikap'] ?>" required min="0" max="100" oninput="hitungOtomatis()">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Komunikasi *</label>
                            <input type="number" class="form-control nilai" name="komunikasi" value="<?= $siswa['komunikasi'] ?>" required min="0" max="100" oninput="hitungOtomatis()">
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <hr>
                            <h5><i class="fas fa-chart-line me-2"></i>Hasil Perhitungan Otomatis</h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rata-rata</label>
                            <input type="text" class="form-control" id="rata_rata" name="rata_rata" value="<?= $siswa['rata_rata'] ?>" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Standar Deviasi</label>
                            <input type="text" class="form-control" id="standar_deviasi" name="standar_deviasi" value="<?= $siswa['standar_deviasi'] ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-gradient btn-primary px-4">
                            <i class="fas fa-save me-2"></i>Update Data
                        </button>
                        <a href="index.php" class="btn btn-light ms-2">
                            <i class="fas fa-times me-2"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function hitungOtomatis() {
    const nilai = document.querySelectorAll('.nilai');
    let total = 0, count = 0, nilaiArr = [];
    
    nilai.forEach(input => {
        let val = parseFloat(input.value) || 0;
        if(val > 0) {
            total += val;
            count++;
            nilaiArr.push(val);
        }
    });
    
    if(count > 0) {
        let rata = total / count;
        document.getElementById('rata_rata').value = rata.toFixed(2);
        
        let variance = 0;
        nilaiArr.forEach(val => variance += Math.pow(val - rata, 2));
        let stdDev = Math.sqrt(variance / count);
        document.getElementById('standar_deviasi').value = stdDev.toFixed(2);
    }
}

// Panggil hitungOtomatis saat load untuk mengisi nilai awal
$(document).ready(function() {
    hitungOtomatis();
});
</script>

<?php include '../includes/footer.php'; ?>