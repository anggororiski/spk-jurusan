<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$title = "Tambah Siswa";
$base_url = "../";
include '../config/koneksi.php';
include '../includes/header.php';
include '../includes/sidebar.php';

// Cek apakah ada error dari session
if(isset($_SESSION['error'])) {
    echo "<div class='alert alert-danger alert-dismissible fade show'>
            <i class='fas fa-exclamation-circle me-2'></i>{$_SESSION['error']}
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
          </div>";
    unset($_SESSION['error']);
}
?>

<div class="main-content">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-user-plus me-2"></i>Tambah Data Siswa</h3>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        
        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="simpan.php" id="formSiswa" onsubmit="return validateForm()">
                    <div class="row">
                        <!-- Data Identitas -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Siswa <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_siswa" required placeholder="Masukkan nama lengkap">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Asal Sekolah <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="jurusan_asal" required placeholder="Contoh: SMP Negeri 1 Jakarta">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kelas <span class="text-danger">*</span></label>
                            <select class="form-select" name="kelas" required>
                                <option value="">Pilih Kelas</option>
                                <option value="VII">VII (7)</option>
                                <option value="VIII">VIII (8)</option>
                                <option value="IX">IX (9)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="jenis_kelamin" value="L" required>
                                    <label class="form-check-label">Laki-laki</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="jenis_kelamin" value="P" required>
                                    <label class="form-check-label">Perempuan</label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- ========== DATA ORANG TUA (UNTUK BEASISWA) ========== -->
                        <div class="col-md-12 mb-3">
                            <hr>
                            <h5><i class="fas fa-users me-2"></i>Data Orang Tua (untuk Beasiswa)</h5>
                            <small class="text-muted">Data ini digunakan untuk menentukan kelayakan beasiswa</small>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Pendidikan Ayah <span class="text-danger">*</span></label>
                            <select class="form-select" name="pendidikan_ayah" required>
                                <option value="">Pilih Pendidikan Ayah</option>
                                <option value="TIDAK TAMAT SD">TIDAK TAMAT SD</option>
                                <option value="SD/MI">SD/MI</option>
                                <option value="SMP/MTS">SMP/MTS</option>
                                <option value="SMA/SMK/MAK">SMA/SMK/MAK</option>
                                <option value="DIPLOMA">DIPLOMA</option>
                                <option value="SARJANA">SARJANA</option>
                                <option value="MAGISTER/DOKTORAL">MAGISTER/DOKTORAL</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Penghasilan Ayah <span class="text-danger">*</span></label>
                            <select class="form-select" name="penghasilan_ayah" required>
                                <option value="">Pilih Penghasilan Ayah</option>
                                <option value="<1000000">&lt; Rp 1000000</option>
                                <option value="1000000 - 2500000">Rp 1000000 - 2500000</option>
                                <option value="2500000 - 3500000">Rp 2500000 - 3500000</option>
                                <option value="3500000 - 4500000">Rp 3500000 - 4500000</option>
                                <option value="> 4500000">&gt; Rp 4500000</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Pendidikan Ibu <span class="text-danger">*</span></label>
                            <select class="form-select" name="pendidikan_ibu" required>
                                <option value="">Pilih Pendidikan Ibu</option>
                                <option value="TIDAK TAMAT SD">TIDAK TAMAT SD</option>
                                <option value="SD/MI">SD/MI</option>
                                <option value="SMP/MTS">SMP/MTS</option>
                                <option value="SMA/SMK/MAK">SMA/SMK/MAK</option>
                                <option value="DIPLOMA">DIPLOMA</option>
                                <option value="SARJANA">SARJANA</option>
                                <option value="MAGISTER/DOKTORAL">MAGISTER/DOKTORAL</option>
                            </select>
                        </div>
                        
                        <!-- ========== NILAI AKADEMIK ========== -->
                        <div class="col-md-12 mb-3">
                            <hr>
                            <h5><i class="fas fa-book me-2"></i>Nilai Akademik (NUS SMP)</h5>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">NUS Matematika <span class="text-danger">*</span></label>
                            <input type="number" class="form-control nilai" name="nus_mtk_smp" required min="0" max="100" oninput="hitungOtomatis()">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">NUS Bahasa Indonesia <span class="text-danger">*</span></label>
                            <input type="number" class="form-control nilai" name="nus_bind_smp" required min="0" max="100" oninput="hitungOtomatis()">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">NUS Bahasa Inggris <span class="text-danger">*</span></label>
                            <input type="number" class="form-control nilai" name="nus_bing_smp" required min="0" max="100" oninput="hitungOtomatis()">
                        </div>
                        
                        <!-- ========== NILAI PERILAKU ========== -->
                        <div class="col-md-12 mb-3">
                            <hr>
                            <h5><i class="fas fa-heart me-2"></i>Nilai Perilaku & Karakter</h5>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Disiplin <span class="text-danger">*</span></label>
                            <input type="number" class="form-control nilai" name="disiplin" required min="0" max="100" oninput="hitungOtomatis()">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tanggung Jawab <span class="text-danger">*</span></label>
                            <input type="number" class="form-control nilai" name="tanggung_jawab" required min="0" max="100" oninput="hitungOtomatis()">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Sikap <span class="text-danger">*</span></label>
                            <input type="number" class="form-control nilai" name="sikap" required min="0" max="100" oninput="hitungOtomatis()">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Komunikasi <span class="text-danger">*</span></label>
                            <input type="number" class="form-control nilai" name="komunikasi" required min="0" max="100" oninput="hitungOtomatis()">
                        </div>
                        
                        <!-- ========== HASIL PERHITUNGAN OTOMATIS ========== -->
                        <div class="col-md-12 mb-3">
                            <hr>
                            <h5><i class="fas fa-chart-line me-2"></i>Hasil Perhitungan</h5>
                            <small class="text-muted">Rata-rata, standar deviasi, dan skor beasiswa akan dihitung otomatis</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Rata-rata</label>
                            <input type="text" class="form-control" id="rata_rata" name="rata_rata" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Standar Deviasi</label>
                            <input type="text" class="form-control" id="standar_deviasi" name="standar_deviasi" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Skor Beasiswa</label>
                            <input type="text" class="form-control" id="skor_beasiswa" name="skor_beasiswa" readonly style="background:#e8f5e9">
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i>Simpan Data
                        </button>
                        <button type="reset" class="btn btn-secondary ms-2">
                            <i class="fas fa-undo me-2"></i>Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function hitungOtomatis() {
    // Hitung rata-rata dan standar deviasi dari 7 nilai
    const nilai = document.querySelectorAll('.nilai');
    let total = 0;
    let count = 0;
    let nilaiArr = [];
    
    nilai.forEach(input => {
        let val = parseFloat(input.value) || 0;
        if(val > 0) {
            total += val;
            count++;
            nilaiArr.push(val);
        }
    });
    
    let rata_rata = 0;
    if(count > 0) {
        rata_rata = total / count;
        document.getElementById('rata_rata').value = rata_rata.toFixed(2);
        
        let variance = 0;
        nilaiArr.forEach(val => {
            variance += Math.pow(val - rata_rata, 2);
        });
        let stdDev = Math.sqrt(variance / count);
        document.getElementById('standar_deviasi').value = stdDev.toFixed(2);
    }
    
    // Hitung Skor Beasiswa
    hitungSkorBeasiswa();
}

function hitungSkorBeasiswa() {
    // Ambil data untuk beasiswa
    let pendidikanIbu = document.querySelector('select[name="pendidikan_ibu"]').value;
    let penghasilanAyah = document.querySelector('select[name="penghasilan_ayah"]').value;
    let rata_rata = parseFloat(document.getElementById('rata_rata').value) || 0;
    let sikap = parseFloat(document.querySelector('input[name="sikap"]').value) || 0;
    
    // Jika data belum lengkap, skip
    if(!pendidikanIbu || !penghasilanAyah) {
        document.getElementById('skor_beasiswa').value = '';
        return;
    }
    
    // Bobot beasiswa
    const bobot = {
        pendidikan_ibu: 0.25,
        penghasilan_ayah: 0.35,
        rata_rata: 0.30,
        sikap: 0.10
    };
    
    // Normalisasi pendidikan ibu
    let skorPendidikanIbu = normalisasiPendidikan(pendidikanIbu);
    
    // Normalisasi penghasilan ayah
    let skorPenghasilan = normalisasiPenghasilan(penghasilanAyah);
    
    // Normalisasi rata-rata (nilai/100)
    let skorRata = rata_rata / 100;
    
    // Normalisasi sikap (nilai/100)
    let skorSikap = sikap / 100;
    
    // Hitung skor akhir (0-1)
    let skor = (bobot.pendidikan_ibu * skorPendidikanIbu) +
               (bobot.penghasilan_ayah * skorPenghasilan) +
               (bobot.rata_rata * skorRata) +
               (bobot.sikap * skorSikap);
    
    // Ubah ke persen (0-100)
    let skorPersen = (skor * 100).toFixed(2);
    document.getElementById('skor_beasiswa').value = skorPersen + '%';
}

function normalisasiPendidikan(pendidikan) {
    const skor = {
        'TIDAK TAMAT SD': 1.0,
        'SD/MI': 0.85,
        'SMP/MTS': 0.65,
        'SMA/SMK/MAK': 0.40,
        'DIPLOMA': 0.25,
        'SARJANA': 0.15,
        'MAGISTER/DOKTORAL': 0.05
    };
    return skor[pendidikan] || 0.5;
}

function normalisasiPenghasilan(penghasilan) {
    const skor = {
        '<1.101.101': 1.0,
        '1.101.101 - 2.510.101': 0.75,
        '2.510.101 - 3.510.101': 0.50,
        '3.510.101 - 4.510.101': 0.30,
        '> 4.510.101': 0.10
    };
    return skor[penghasilan] || 0.5;
}

function validateForm() {
    let nilai = document.querySelectorAll('.nilai');
    for(let input of nilai) {
        let val = parseFloat(input.value);
        if(val < 0 || val > 100) {
            alert('Nilai harus antara 0-100!');
            input.focus();
            return false;
        }
    }
    
    // Validasi data orang tua
    let pendidikanIbu = document.querySelector('select[name="pendidikan_ibu"]').value;
    let penghasilanAyah = document.querySelector('select[name="penghasilan_ayah"]').value;
    
    if(!pendidikanIbu || !penghasilanAyah) {
        alert('Harap lengkapi data pendidikan ibu dan penghasilan ayah!');
        return false;
    }
    
    return true;
}

// Event listener untuk perubahan data orang tua
document.addEventListener('DOMContentLoaded', function() {
    // Pasang event listener
    let pendidikanIbuSelect = document.querySelector('select[name="pendidikan_ibu"]');
    let penghasilanAyahSelect = document.querySelector('select[name="penghasilan_ayah"]');
    let sikapInput = document.querySelector('input[name="sikap"]');
    
    if(pendidikanIbuSelect) pendidikanIbuSelect.addEventListener('change', hitungSkorBeasiswa);
    if(penghasilanAyahSelect) penghasilanAyahSelect.addEventListener('change', hitungSkorBeasiswa);
    if(sikapInput) sikapInput.addEventListener('input', hitungSkorBeasiswa);
    
    // Panggil sekali untuk inisialisasi
    hitungOtomatis();
});
</script>

<style>
/* Style tambahan jika perlu */
</style>

<?php include '../includes/footer.php'; ?>