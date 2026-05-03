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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// Cek apakah export khusus beasiswa
$export_beasiswa = isset($_GET['type']) && $_GET['type'] == 'beasiswa';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Judul sesuai jenis export
if($export_beasiswa) {
    $sheet->setCellValue('A1', 'LAPORAN SISWA LAYAK BEASISWA');
    $sheet->mergeCells('A1:P1');
    
    $headers = [
        'No', 'Nama Siswa', 'Asal Sekolah', 'Kelas', 'JK', 
        'Pendidikan Ibu', 'Penghasilan Ayah', 'Skor Beasiswa', 'Status',
        'MTK', 'B.Indo', 'B.Ing', 'Rata-rata', 'Rekomendasi Jurusan'
    ];
    $columns = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N'];
} else {
    $sheet->setCellValue('A1', 'LAPORAN DATA SISWA');
    $sheet->mergeCells('A1:O1');
    
    $headers = [
        'No', 'Nama Siswa', 'Asal Sekolah', 'Kelas', 'JK', 'Penghasilan',
        'MTK', 'B.Indo', 'B.Ing', 'Disiplin', 'Tanggung Jawab', 'Sikap', 'Komunikasi',
        'Rata-rata', 'Rekomendasi Jurusan'
    ];
    $columns = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O'];
}

$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A2', 'Tanggal Export: ' . date('d/m/Y H:i:s'));
$sheet->mergeCells('A2:' . end($columns) . '2');

// Header tabel
foreach($columns as $index => $col) {
    $sheet->setCellValue($col . '4', $headers[$index]);
}

// Style header
$lastCol = end($columns);
$sheet->getStyle('A4:' . $lastCol . '4')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
]);

// Query data
if($export_beasiswa) {
    $query = "SELECT * FROM siswa WHERE rekomendasi_beasiswa = 'Ya' ORDER BY skor_beasiswa DESC";
} else {
    $query = "SELECT * FROM siswa ORDER BY rata_rata DESC";
}
$siswa = mysqli_query($conn, $query);

$row = 5;
$no = 1;

while($d = mysqli_fetch_assoc($siswa)) {
    if($export_beasiswa) {
        // Export khusus beasiswa
        $status = $d['rekomendasi_beasiswa'] ?? 'Tidak';
        $skor = $d['skor_beasiswa'] ?? 0;
        
        $sheet->setCellValue('A' . $row, $no++);
        $sheet->setCellValue('B' . $row, $d['nama_siswa']);
        $sheet->setCellValue('C' . $row, $d['jurusan_asal'] ?? '-');
        $sheet->setCellValue('D' . $row, $d['kelas']);
        $sheet->setCellValue('E' . $row, $d['jenis_kelamin']);
        $sheet->setCellValue('F' . $row, $d['pendidikan_ibu'] ?? '-');
        $sheet->setCellValue('G' . $row, $d['penghasilan_ayah'] ?? '-');
        $sheet->setCellValue('H' . $row, number_format($skor, 2) . '%');
        $sheet->setCellValue('I' . $row, $status);
        $sheet->setCellValue('J' . $row, $d['nus_mtk_smp']);
        $sheet->setCellValue('K' . $row, $d['nus_bind_smp']);
        $sheet->setCellValue('L' . $row, $d['nus_bing_smp']);
        $sheet->setCellValue('M' . $row, round($d['rata_rata'], 2));
        $sheet->setCellValue('N' . $row, $d['rekomendasi_jurusan'] ?? 'Belum');
    } else {
        // Export semua data (seperti sebelumnya)
        $sheet->setCellValue('A' . $row, $no++);
        $sheet->setCellValue('B' . $row, $d['nama_siswa']);
        $sheet->setCellValue('C' . $row, $d['jurusan_asal']);
        $sheet->setCellValue('D' . $row, $d['kelas']);
        $sheet->setCellValue('E' . $row, $d['jenis_kelamin']);
        $sheet->setCellValue('F' . $row, $d['penghasilan_ayah']);
        $sheet->setCellValue('G' . $row, $d['nus_mtk_smp']);
        $sheet->setCellValue('H' . $row, $d['nus_bind_smp']);
        $sheet->setCellValue('I' . $row, $d['nus_bing_smp']);
        $sheet->setCellValue('J' . $row, $d['disiplin']);
        $sheet->setCellValue('K' . $row, $d['tanggung_jawab']);
        $sheet->setCellValue('L' . $row, $d['sikap']);
        $sheet->setCellValue('M' . $row, $d['komunikasi']);
        $sheet->setCellValue('N' . $row, round($d['rata_rata'], 2));
        $sheet->setCellValue('O' . $row, $d['rekomendasi_jurusan'] ?? 'Belum');
        
        // Format currency (hanya untuk export biasa)
        $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    }
    $row++;
}

// Border untuk data
$sheet->getStyle('A4:' . $lastCol . ($row-1))->applyFromArray([
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
]);

// Auto size
foreach(range('A', $lastCol) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Nama file
if($export_beasiswa) {
    $filename = 'laporan_beasiswa_' . date('Ymd_His') . '.xlsx';
} else {
    $filename = 'data_siswa_' . date('Ymd_His') . '.xlsx';
}

// Download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>