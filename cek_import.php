<?php
echo "<h2>Cek Status Import Excel</h2>";

if(file_exists('vendor/autoload.php')) {
    echo "<p style='color:green'>✓ PhpSpreadsheet sudah terinstall</p>";
    require_once 'vendor/autoload.php';
    echo "<p style='color:green'>✓ PhpSpreadsheet berhasil dimuat</p>";
} else {
    echo "<p style='color:red'>✗ PhpSpreadsheet belum terinstall!</p>";
    echo "<p>Silakan install composer terlebih dahulu, lalu jalankan: <strong>composer require phpoffice/phpspreadsheet</strong></p>";
}

echo "<h3>Struktur Folder:</h3>";
echo "<pre>";
system("dir /b C:\\xampp\\htdocs\\spk-jurusan");
echo "</pre>";
?>