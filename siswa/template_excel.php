<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

// Pastikan PhpSpreadsheet sudah diinstall
// Jika belum, jalankan: composer require phpoffice/phpspreadsheet

require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Cek apakah file vendor ada
if(!file_exists('../vendor/autoload.php')) {
    die("Error: PhpSpreadsheet tidak ditemukan. Silakan install composer terlebih dahulu.<br>
         Jalankan perintah: <strong>composer require phpoffice/phpspreadsheet</strong> di folder spk-jurusan");
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set judul
$sheet->setCellValue('A1', 'TEMPLATE IMPORT DATA SISWA');
$sheet->mergeCells('A1:L1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Header kolom
$headers = [
    'A' => 'Nama Siswa',
    'B' => 'Asal Sekolah',
    'C' => 'Kelas',
    'D' => 'Jenis Kelamin',
    'E' => 'Penghasilan Ayah',
    'F' => 'NUS Matematika',
    'G' => 'NUS B.Indonesia',
    'H' => 'NUS B.Inggris',
    'I' => 'Disiplin',
    'J' => 'Tanggung Jawab',
    'K' => 'Sikap',
    'L' => 'Komunikasi'
];

$rowHeader = 3;
foreach($headers as $col => $header) {
    $sheet->setCellValue($col . $rowHeader, $header);
}

// Style header
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
];
$sheet->getStyle('A3:L3')->applyFromArray($headerStyle);

// Contoh data
$contoh = [
    ['Budi Santoso', 'SMPN 1 Jakarta', 'IX', 'L', 3500000, 85, 80, 75, 90, 85, 88, 82],
    ['Siti Aminah', 'SMPN 2 Jakarta', 'IX', 'P', 5000000, 90, 85, 88, 95, 90, 92, 88],
    ['Ahmad Hidayat', 'SMPN 3 Jakarta', 'IX', 'L', 2500000, 75, 70, 72, 80, 78, 75, 70]
];

$rowData = 4;
foreach($contoh as $data) {
    $sheet->setCellValue('A' . $rowData, $data[0]);
    $sheet->setCellValue('B' . $rowData, $data[1]);
    $sheet->setCellValue('C' . $rowData, $data[2]);
    $sheet->setCellValue('D' . $rowData, $data[3]);
    $sheet->setCellValue('E' . $rowData, $data[4]);
    $sheet->setCellValue('F' . $rowData, $data[5]);
    $sheet->setCellValue('G' . $rowData, $data[6]);
    $sheet->setCellValue('H' . $rowData, $data[7]);
    $sheet->setCellValue('I' . $rowData, $data[8]);
    $sheet->setCellValue('J' . $rowData, $data[9]);
    $sheet->setCellValue('K' . $rowData, $data[10]);
    $sheet->setCellValue('L' . $rowData, $data[11]);
    $rowData++;
}

// Style contoh data
$sheet->getStyle('A4:L' . ($rowData-1))->applyFromArray([
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]]
]);

// Auto size kolom
foreach(range('A','L') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Catatan
$rowCatatan = $rowData + 2;
$sheet->setCellValue('A' . $rowCatatan, 'CATATAN:');
$sheet->getStyle('A' . $rowCatatan)->getFont()->setBold(true);
$sheet->setCellValue('A' . ($rowCatatan + 1), '1. Isi data sesuai format di atas (jangan ubah struktur kolom)');
$sheet->setCellValue('A' . ($rowCatatan + 2), '2. Jenis Kelamin: L (Laki-laki) atau P (Perempuan)');
$sheet->setCellValue('A' . ($rowCatatan + 3), '3. Nilai harus antara 0-100');
$sheet->setCellValue('A' . ($rowCatatan + 4), '4. Kolom Rata-rata dan Standar Deviasi akan dihitung otomatis oleh sistem');
$sheet->setCellValue('A' . ($rowCatatan + 5), '5. Baris pertama (header) tidak akan diimport');

// Download file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="template_import_siswa.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>