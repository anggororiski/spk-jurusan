<?php
session_start();
include 'config/koneksi.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    
    // Untuk demo, gunakan username: admin, password: admin123
    if($username == 'admin' && $password == 'admin123') {
        $_SESSION['user'] = 'admin';
        $_SESSION['nama'] = 'Administrator';
        header("Location: dashboard.php");
        exit;
    } else {
        header("Location: login.php?error=1");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}
?>