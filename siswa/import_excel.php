<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

include '../config/koneksi.php';

// Cek apakah ada file yang diupload
if($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_FILES['file_excel'])) {
    $_SESSION['error'] = "Tidak ada file yang diupload";
    header("Location: import.php");
    exit;
}

// Cek error upload
if($_FILES['file_excel']['error'] != 0) {
    $_SESSION['error'] = "Error upload file: " . $_FILES['file_excel']['error'];
    header("Location: import.php");
    exit;
}

// Cek ekstensi file
$ext = pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION);
if(!in_array($ext, ['xls', 'xlsx'])) {
    $_SESSION['error'] = "Format file harus .xls atau .xlsx";
    header("Location: import.php");
    exit;
}

// Cek ukuran file (max 2MB)
if($_FILES['file_excel']['size'] > 2 * 1024 * 1024) {
    $_SESSION['error'] = "Ukuran file terlalu besar. Maksimal 2MB";
    header("Location: import.php");
    exit;
}

require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $file = $_FILES['file_excel']['tmp_name'];
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();
    
    if(count($rows) <= 1) {
        $_SESSION['error'] = "File Excel tidak berisi data";
        header("Location: import.php");
        exit;
    }
    
    // Hapus header (baris pertama)
    array_shift($rows);
    
    $success = 0;
    $failed = 0;
    $errors = [];
    
    foreach($rows as $index => $row) {
        // Lewati baris kosong
        if(empty(array_filter($row))) continue;
        
        $nama_siswa = trim($row[0] ?? '');
        $jurusan_asal = trim($row[1] ?? '');
        $kelas = trim($row[2] ?? '');
        $jenis_kelamin = trim($row[3] ?? '');
        $penghasilan_ayah = floatval($row[4] ?? 0);
        $nus_mtk_smp = floatval($row[5] ?? 0);
        $nus_bind_smp = floatval($row[6] ?? 0);
        $nus_bing_smp = floatval($row[7] ?? 0);
        $disiplin = floatval($row[8] ?? 0);
        $tanggung_jawab = floatval($row[9] ?? 0);
        $sikap = floatval($row[10] ?? 0);
        $komunikasi = floatval($row[11] ?? 0);
        
        // Validasi data wajib
        if(empty($nama_siswa)) {
            $failed++;
            $errors[] = "Baris " . ($index + 2) . ": Nama siswa tidak boleh kosong";
            continue;
        }
        
        // Validasi nilai
        if($nus_mtk_smp < 0 || $nus_mtk_smp > 100 || 
           $nus_bind_smp < 0 || $nus_bind_smp > 100 ||
           $nus_bing_smp < 0 || $nus_bing_smp > 100 ||
           $disiplin < 0 || $disiplin > 100 ||
           $tanggung_jawab < 0 || $tanggung_jawab > 100 ||
           $sikap < 0 || $sikap > 100 ||
           $komunikasi < 0 || $komunikasi > 100) {
            $failed++;
            $errors[] = "Baris " . ($index + 2) . ": Nilai harus antara 0-100";
            continue;
        }
        
        // Hitung rata-rata
        $nilai_arr = [$nus_mtk_smp, $nus_bind_smp, $nus_bing_smp, $disiplin, $tanggung_jawab, $sikap, $komunikasi];
        $rata_rata = array_sum($nilai_arr) / count($nilai_arr);
        
        // Hitung standar deviasi
        $variance = 0;
        foreach($nilai_arr as $nilai) {
            $variance += pow($nilai - $rata_rata, 2);
        }
        $std_dev = sqrt($variance / count($nilai_arr));
        
        // Query insert
        $query = "INSERT INTO siswa (
            nama_siswa, jurusan_asal, kelas, jenis_kelamin,
            penghasilan_ayah, nus_mtk_smp, nus_bind_smp, nus_bing_smp,
            disiplin, tanggung_jawab, sikap, komunikasi,
            rata_rata, standar_deviasi
        ) VALUES (
            '$nama_siswa', '$jurusan_asal', '$kelas', '$jenis_kelamin',
            '$penghasilan_ayah', '$nus_mtk_smp', '$nus_bind_smp', '$nus_bing_smp',
            '$disiplin', '$tanggung_jawab', '$sikap', '$komunikasi',
            '$rata_rata', '$std_dev'
        )";
        
        if(mysqli_query($conn, $query)) {
            $success++;
        } else {
            $failed++;
            $errors[] = "Baris " . ($index + 2) . ": " . mysqli_error($conn);
        }
    }
    
    if($success > 0) {
        $_SESSION['success'] = "Berhasil mengimport $success data siswa" . ($failed > 0 ? ", gagal $failed data" : "");
        if(!empty($errors)) {
            $_SESSION['import_errors'] = $errors;
        }
    } else {
        $_SESSION['error'] = "Tidak ada data yang berhasil diimport";
        if(!empty($errors)) {
            $_SESSION['import_errors'] = $errors;
        }
    }
    
} catch(Exception $e) {
    $_SESSION['error'] = "Error membaca file: " . $e->getMessage();
}

header("Location: index.php");
exit;
?>