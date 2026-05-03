<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

include '../config/koneksi.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Fungsi hitung skor
function hitungSkorExport($siswa, $jurusan, $conn) {
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

// Ambil data
$siswa_list = mysqli_query($conn, "SELECT * FROM siswa ORDER BY nama_siswa ASC");
$hasil = [];

while($siswa = mysqli_fetch_assoc($siswa_list)) {
    $skor_dkv = hitungSkorExport($siswa, 'DKV', $conn);
    $skor_tkr = hitungSkorExport($siswa, 'TKR', $conn);
    
    if($skor_dkv >= $skor_tkr) {
        $jurusan = 'DKV';
        $skor = $skor_dkv;
    } else {
        $jurusan = 'TKR';
        $skor = $skor_tkr;
    }
    
    if($skor >= 0.85) $kecocokan = 'Sangat Cocok';
    elseif($skor >= 0.70) $kecocokan = 'Cocok';
    elseif($skor >= 0.55) $kecocokan = 'Cukup Cocok';
    else $kecocokan = 'Kurang Cocok';
    
    $hasil[] = [
        'nama' => $siswa['nama_siswa'],
        'asal' => $siswa['jurusan_asal'],
        'kelas' => $siswa['kelas'],
        'skor_dkv' => $skor_dkv,
        'skor_tkr' => $skor_tkr,
        'jurusan' => $jurusan,
        'kecocokan' => $kecocokan
    ];
}

usort($hasil, function($a, $b) {
    return max($b['skor_dkv'], $b['skor_tkr']) <=> max($a['skor_dkv'], $a['skor_tkr']);
});

// Buat Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('A1', 'HASIL SPK PEMILIHAN JURUSAN SMK');
$sheet->mergeCells('A1:G1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A2', 'Tanggal Export: ' . date('d/m/Y H:i:s'));
$sheet->mergeCells('A2:G2');

$headers = ['Rank', 'Nama Siswa', 'Asal Sekolah', 'Kelas', 'Skor DKV', 'Skor TKR', 'Rekomendasi', 'Kecocokan'];
$columns = ['A','B','C','D','E','F','G','H'];

foreach($columns as $index => $col) {
    $sheet->setCellValue($col . '4', $headers[$index]);
}

$sheet->getStyle('A4:H4')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
]);

$row = 5;
$rank = 1;
foreach($hasil as $h) {
    $sheet->setCellValue('A' . $row, $rank++);
    $sheet->setCellValue('B' . $row, $h['nama']);
    $sheet->setCellValue('C' . $row, $h['asal']);
    $sheet->setCellValue('D' . $row, $h['kelas']);
    $sheet->setCellValue('E' . $row, round($h['skor_dkv'] * 100, 2) . '%');
    $sheet->setCellValue('F' . $row, round($h['skor_tkr'] * 100, 2) . '%');
    $sheet->setCellValue('G' . $row, $h['jurusan']);
    $sheet->setCellValue('H' . $row, $h['kecocokan']);
    $row++;
}

$sheet->getStyle('A4:H' . ($row-1))->applyFromArray([
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
]);

foreach(range('A','H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="hasil_spk_' . date('Ymd_His') . '.xlsx"');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>