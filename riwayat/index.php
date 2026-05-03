<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

include '../config/koneksi.php'; // ✅ koneksi dulu

// ✅ AJAX handler di atas sebelum output apapun
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if(isset($_POST['action']) && $_POST['action'] == 'hapus' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        if(mysqli_query($conn, "DELETE FROM riwayat WHERE id = $id")) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus']);
        }
        exit;
    }
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// ✅ Query $riwayat di sini, setelah koneksi tersedia
$riwayat = mysqli_query($conn, "SELECT * FROM riwayat ORDER BY waktu DESC");

$title = "Riwayat Perhitungan";
$base_url = "../";
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <h3 class="mb-4"><i class="fas fa-history me-2"></i>Riwayat Perhitungan SPK</h3>
        
        <?php if(mysqli_num_rows($riwayat) == 0): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Belum ada riwayat perhitungan. Silakan lakukan perhitungan SPK terlebih dahulu.
            </div>
        <?php else: ?>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="tabelRiwayat">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Waktu Perhitungan</th>
                                <th>Total Siswa</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1; 
                            while($row = mysqli_fetch_assoc($riwayat)): 
                                $data_array = json_decode($row['data'], true);
                                if(!$data_array) $data_array = [];
                                $data_json_attr = htmlspecialchars(json_encode($data_array), ENT_QUOTES, 'UTF-8');
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= date('d/m/Y H:i:s', strtotime($row['waktu'])) ?></td>
                                <td><?= $row['total_siswa'] ?> siswa</td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="lihatDetail(this)" data-data='<?= $data_json_attr ?>'>
                                        <i class="fas fa-eye"></i> Lihat Detail
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="hapusRiwayat(<?= $row['id'] ?>)">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <?php endif; ?>
    </div>
</div>

<!-- Modal Detail Riwayat -->
<div class="modal fade" id="modalDetailRiwayat" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-history me-2"></i>Detail Riwayat Perhitungan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>Skor DKV</th>
                                <th>Skor TKR</th>
                                <th>Rekomendasi Jurusan</th>
                                <th>Kecocokan</th>
                                <th>Skor Beasiswa</th>
                                <th>Status Beasiswa</th>
                            </tr>
                        </thead>
                        <tbody id="detailBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#tabelRiwayat').DataTable({
        responsive: true,
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' },
        order: [[1, 'desc']]
    });
});

function lihatDetail(btn) {
    let dataJson = btn.getAttribute('data-data');
    
    if(!dataJson || dataJson === 'null' || dataJson === '[]') {
        Swal.fire('Error', 'Data tidak ditemukan', 'error');
        return;
    }
    
    try {
        let data = JSON.parse(dataJson);
        let html = '';
        let no = 1;
        
        data.forEach(item => {
            let warnaJurusan = item.rekomendasi == 'DKV' ? 'primary' : 'danger';
            
            let warnaKecocokan = 'secondary';
            if(item.kecocokan == 'Sangat Cocok') warnaKecocokan = 'success';
            else if(item.kecocokan == 'Cocok') warnaKecocokan = 'primary';
            else if(item.kecocokan == 'Cukup Cocok') warnaKecocokan = 'warning';
            
            let skorBeasiswa = item.skor_beasiswa || 0;
            let statusBeasiswa = skorBeasiswa >= 65 ? 'Ya' : 'Tidak';
            let warnaBeasiswa = statusBeasiswa == 'Ya' ? 'success' : 'secondary';
            let iconBeasiswa = statusBeasiswa == 'Ya' ? '✅' : '❌';
            
            html += '<tr>' +
                '<td>' + (no++) + '</td>' +
                '<td>' + escapeHtml(item.nama) + '</td>' +
                '<td>' + (item.skor_dkv || 0) + '%</td>' +
                '<td>' + (item.skor_tkr || 0) + '%</td>' +
                '<td><span class="badge bg-' + warnaJurusan + '">' + (item.rekomendasi || '-') + '</span></td>' +
                '<td><span class="badge bg-' + warnaKecocokan + '">' + (item.kecocokan || '-') + '</span></td>' +
                '<td><strong>' + parseFloat(skorBeasiswa).toFixed(2) + '%</strong></td>' +
                '<td><span class="badge bg-' + warnaBeasiswa + '">' + iconBeasiswa + ' ' + statusBeasiswa + '</span></td>' +
                '</tr>';
        });
        
        $('#detailBody').html(html);
        $('#modalDetailRiwayat').modal('show');
    } catch(e) {
        console.error(e);
        Swal.fire('Error', 'Gagal memuat detail: ' + e.message, 'error');
    }
}

function hapusRiwayat(id) {
    Swal.fire({
        title: 'Hapus Riwayat?',
        text: 'Data riwayat akan dihapus permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if(result.isConfirmed) {
            $.ajax({
                url: window.location.href,
                type: 'POST',
                data: { action: 'hapus', id: id },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        Swal.fire('Berhasil!', 'Riwayat dihapus', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Gagal!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Terjadi kesalahan', 'error');
                }
            });
        }
    });
}

function escapeHtml(str) {
    if(!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if(m === '&') return '&amp;';
        if(m === '<') return '&lt;';
        if(m === '>') return '&gt;';
        return m;
    });
}
</script>

<?php include '../includes/footer.php'; ?>