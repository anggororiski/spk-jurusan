<?php
// Konfigurasi database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "spk_jurusan";

// Koneksi database
$conn = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8");

// Nonaktifkan error reporting untuk production (opsional)
// mysqli_report(MYSQLI_REPORT_ERROR);

// Fungsi untuk membersihkan input
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

// Fungsi untuk redirect
function redirect($url) {
    header("Location: $url");
    exit;
}

// Fungsi untuk menampilkan notifikasi
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Fungsi untuk menampilkan flash message
function show_flash() {
    if(isset($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'];
        $message = $_SESSION['flash']['message'];
        echo "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
                <i class='fas " . ($type == 'success' ? 'fa-check-circle' : ($type == 'danger' ? 'fa-exclamation-circle' : 'fa-info-circle')) . " me-2'></i>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
        unset($_SESSION['flash']);
    }
}
?>