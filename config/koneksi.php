<?php
// Konfigurasi database - support Railway (env vars) dan hosting biasa (hardcode)
$host = getenv('MYSQLHOST')     ?: getenv('DB_HOST')     ?: 'localhost';
$user = getenv('MYSQLUSER')     ?: getenv('DB_USER')     ?: 'if0_41810587';
$pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASSWORD') ?: 'ruDNL9SgZI';
$db   = getenv('MYSQLDATABASE') ?: getenv('DB_NAME')     ?: 'if0_41810587_spk_jurusan';
$port = (int)(getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: 3306);

// Koneksi database
$conn = mysqli_connect($host, $user, $pass, $db, $port);

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8");

// Fungsi untuk membersihkan input
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

// Fungsi redirect
function redirect($url) {
    header("Location: $url");
    exit;
}

// Fungsi flash message
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function show_flash() {
    if (isset($_SESSION['flash'])) {
        $type    = $_SESSION['flash']['type'];
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
