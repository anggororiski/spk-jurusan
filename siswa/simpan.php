<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

include '../config/koneksi.php';

// ========== FUNGSI HITUNG REKOMENDASI JURUSAN ==========
function hitungRekomendasiJurusan($mtk, $bind, $bing, $disiplin, $tanggung_jawab, $sikap, $komunikasi) {
    
    $bobot = [
        'DKV' => ['mtk' => 0.20, 'bind' => 0.15, 'bing' => 0.15, 'disiplin' => 0.15, 'tanggung_jawab' => 0.10, 'sikap' => 0.10, 'komunikasi' => 0.15],
        'TKR' => ['mtk' => 0.25, 'bind' => 0.10, 'bing' => 0.10, 'disiplin' => 0.20, 'tanggung_jawab' => 0.15, 'sikap' => 0.10, 'komunikasi' => 0.10]
    ];
    
    $nilai = [
        'mtk' => $mtk / 100,
        'bind' => $bind / 100,
        'bing' => $bing / 100,
        'disiplin' => $disiplin / 100,
        'tanggung_jawab' => $tanggung_jawab / 100,
        'sikap' => $sikap / 100,
        'komunikasi' => $komunikasi / 100
    ];
    
    $skor = [];
    foreach($bobot as $jurusan => $bobot_jurusan) {
        $total = 0;
        $total += $nilai['mtk'] * $bobot_jurusan['mtk'];
        $total += $nilai['bind'] * $bobot_jurusan['bind'];
        $total += $nilai['bing'] * $bobot_jurusan['bing'];
        $total += $nilai['disiplin'] * $bobot_jurusan['disiplin'];
        $total += $nilai['tanggung_jawab'] * $bobot_jurusan['tanggung_jawab'];
        $total += $nilai['sikap'] * $bobot_jurusan['sikap'];
        $total += $nilai['komunikasi'] * $bobot_jurusan['komunikasi'];
        $skor[$jurusan] = $total * 100;
    }
    
    // Cari jurusan dengan skor tertinggi
    $jurusan_terbaik = array_keys($skor, max($skor))[0];
    return $jurusan_terbaik;
}

// ========== FUNGSI BEASISWA ==========
function normalisasiPendidikan($pendidikan) {
    $skor = [
        'TIDAK TAMAT SD' => 1.0,
        'SD/MI' => 0.85,
        'SMP/MTS' => 0.65,
        'SMA/SMK/MAK' => 0.40,
        'DIPLOMA' => 0.25,
        'SARJANA' => 0.15,
        'MAGISTER/DOKTORAL' => 0.05
    ];
    return isset($skor[$pendidikan]) ? $skor[$pendidikan] : 0.5;
}

function normalisasiPenghasilan($penghasilan) {
    $skor = [
        '<1.101.101' => 1.0,
        '1.101.101 - 2.510.101' => 0.75,
        '2.510.101 - 3.510.101' => 0.50,
        '3.510.101 - 4.510.101' => 0.30,
        '> 4.510.101' => 0.10
    ];
    return isset($skor[$penghasilan]) ? $skor[$penghasilan] : 0.5;
}

function hitungSkorBeasiswa($pendidikan_ibu, $penghasilan_ayah, $rata_rata, $sikap) {
    $bobot = [
        'pendidikan_ibu' => 0.25,
        'penghasilan_ayah' => 0.35,
        'rata_rata' => 0.30,
        'sikap' => 0.10
    ];
    
    $skorPendidikan = normalisasiPendidikan($pendidikan_ibu);
    $skorPenghasilan = normalisasiPenghasilan($penghasilan_ayah);
    $skorRata = $rata_rata / 100;
    $skorSikap = $sikap / 100;
    
    $skor = ($bobot['pendidikan_ibu'] * $skorPendidikan) +
            ($bobot['penghasilan_ayah'] * $skorPenghasilan) +
            ($bobot['rata_rata'] * $skorRata) +
            ($bobot['sikap'] * $skorSikap);
    
    return $skor * 100;
}

// ========== CEK METHOD ==========
if($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: tambah.php");
    exit;
}

// ========== AMBIL DATA DARI FORM ==========
$nama_siswa = mysqli_real_escape_string($conn, $_POST['nama_siswa']);
$jurusan_asal = mysqli_real_escape_string($conn, $_POST['jurusan_asal']);
$kelas = mysqli_real_escape_string($conn, $_POST['kelas']);
$jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
$pendidikan_ayah = mysqli_real_escape_string($conn, $_POST['pendidikan_ayah']);
$pendidikan_ibu = mysqli_real_escape_string($conn, $_POST['pendidikan_ibu']);
$penghasilan_ayah = mysqli_real_escape_string($conn, $_POST['penghasilan_ayah']);
$nus_mtk_smp = (int)$_POST['nus_mtk_smp'];
$nus_bind_smp = (int)$_POST['nus_bind_smp'];
$nus_bing_smp = (int)$_POST['nus_bing_smp'];
$disiplin = (int)$_POST['disiplin'];
$tanggung_jawab = (int)$_POST['tanggung_jawab'];
$sikap = (int)$_POST['sikap'];
$komunikasi = (int)$_POST['komunikasi'];
$rata_rata = (float)$_POST['rata_rata'];
$standar_deviasi = (float)$_POST['standar_deviasi'];

// ========== HITUNG REKOMENDASI JURUSAN ==========
$rekomendasi_jurusan = hitungRekomendasiJurusan(
    $nus_mtk_smp, $nus_bind_smp, $nus_bing_smp,
    $disiplin, $tanggung_jawab, $sikap, $komunikasi
);

// ========== HITUNG SKOR BEASISWA ==========
$skor_beasiswa = hitungSkorBeasiswa($pendidikan_ibu, $penghasilan_ayah, $rata_rata, $sikap);
$rekomendasi_beasiswa = $skor_beasiswa >= 50 ? 'Ya' : 'Tidak';

// ========== VALIDASI ==========
if(empty($nama_siswa) || empty($jurusan_asal) || empty($kelas) || empty($jenis_kelamin)) {
    $_SESSION['error'] = "Data wajib tidak boleh kosong!";
    header("Location: tambah.php");
    exit;
}

// ========== QUERY SIMPAN ==========
$query = "INSERT INTO siswa (
    nama_siswa, jurusan_asal, kelas, jenis_kelamin,
    pendidikan_ayah, pendidikan_ibu, penghasilan_ayah,
    nus_mtk_smp, nus_bind_smp, nus_bing_smp,
    disiplin, tanggung_jawab, sikap, komunikasi,
    rata_rata, standar_deviasi,
    rekomendasi_jurusan, skor_beasiswa, rekomendasi_beasiswa
) VALUES (
    '$nama_siswa', '$jurusan_asal', '$kelas', '$jenis_kelamin',
    '$pendidikan_ayah', '$pendidikan_ibu', '$penghasilan_ayah',
    '$nus_mtk_smp', '$nus_bind_smp', '$nus_bing_smp',
    '$disiplin', '$tanggung_jawab', '$sikap', '$komunikasi',
    '$rata_rata', '$standar_deviasi',
    '$rekomendasi_jurusan', '$skor_beasiswa', '$rekomendasi_beasiswa'
)";

if(mysqli_query($conn, $query)) {
    $_SESSION['success'] = "Data siswa berhasil ditambahkan!";
    header("Location: index.php?success=Data berhasil ditambahkan");
} else {
    $_SESSION['error'] = "Gagal menambah data: " . mysqli_error($conn);
    header("Location: tambah.php");
}
exit;
?>