<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Content-Type: application/json");
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include '../config/koneksi.php';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ids'])) {
    $ids = $_POST['ids'];
    
    if(empty($ids)) {
        echo json_encode(['success' => false, 'message' => 'Tidak ada data yang dipilih']);
        exit;
    }
    
    // Escape dan gabungkan ID
    $ids_escaped = array_map(function($id) use ($conn) {
        return "'" . mysqli_real_escape_string($conn, $id) . "'";
    }, $ids);
    
    $ids_string = implode(',', $ids_escaped);
    
    // Hapus data
    $query = "DELETE FROM siswa WHERE id IN ($ids_string)";
    
    if(mysqli_query($conn, $query)) {
        $jumlah = mysqli_affected_rows($conn);
        echo json_encode(['success' => true, 'message' => "Berhasil menghapus $jumlah data siswa"]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus data: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>