<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$title = "Data Siswa";
$base_url = "../";
include '../config/koneksi.php';
include '../includes/header.php';
include '../includes/sidebar.php';

?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-users me-2"></i>Data Siswa</h3>
            <div class="d-flex flex-wrap gap-2">
                <!-- Dropdown untuk Tools -->
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-tools me-1"></i> Tools
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a href="import.php" class="dropdown-item">
                                <i class="fas fa-file-excel text-success me-2"></i> Import Excel
                            </a>
                        </li>
                        <li>
                            <a href="export_excel.php" class="dropdown-item">
                                <i class="fas fa-download text-info me-2"></i> Export Excel
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Tombol Tambah -->
                <a href="tambah.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah
                </a>
                
                <!-- Tombol Hapus Semua -->
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalHapusSemua">
                    <i class="fas fa-trash-alt"></i> Hapus
                </button>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="tabelSiswa">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="checkAll"></th>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Asal Sekolah</th>
                                <th>Kelas</th>
                                <th>JK</th>
                                <th>MTK</th>
                                <th>B.Indo</th>
                                <th>B.Ing</th>
                                <th>Rata</th>
                                <th>Rekomendasi Jurusan</th>
                                <th>Skor Beasiswa</th>
                                <th>Beasiswa</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $data = mysqli_query($conn, "SELECT id, nama_siswa, jurusan_asal, kelas, jenis_kelamin, nus_mtk_smp, nus_bind_smp, nus_bing_smp, rata_rata, rekomendasi_jurusan, skor_beasiswa, rekomendasi_beasiswa FROM siswa ORDER BY rata_rata DESC");
                            while($d = mysqli_fetch_assoc($data)):
                                $warna = $d['rekomendasi_jurusan'] == 'DKV' ? 'primary' : 'danger';
                                $beasiswa_warna = $d['rekomendasi_beasiswa'] == 'Ya' ? 'success' : 'secondary';
                                $beasiswa_icon = $d['rekomendasi_beasiswa'] == 'Ya' ? '✅' : '❌';
                            ?>
                            <tr>
                                <td><input type="checkbox" class="checkItem" value="<?= $d['id'] ?>"></td>
                                <td><?= $no++ ?></td>
                                <td><strong><?= htmlspecialchars($d['nama_siswa']) ?></strong></td>
                                <td><?= htmlspecialchars($d['jurusan_asal']) ?></td>
                                <td><?= $d['kelas'] ?></td>
                                <td><?= $d['jenis_kelamin'] ?></td>
                                <td><?= $d['nus_mtk_smp'] ?></td>
                                <td><?= $d['nus_bind_smp'] ?></td>
                                <td><?= $d['nus_bing_smp'] ?></td>
                                <td><strong><?= round($d['rata_rata'], 2) ?></strong></td>
                                <td>
                                    <?php if($d['rekomendasi_jurusan']): ?>
                                        <span class="badge bg-<?= $warna ?>"><?= $d['rekomendasi_jurusan'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Belum</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($d['skor_beasiswa'] > 0): ?>
                                        <strong><?= number_format($d['skor_beasiswa'], 2) ?>%</strong>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($d['rekomendasi_beasiswa']): ?>
                                        <span class="badge bg-<?= $beasiswa_warna ?>">
                                            <?= $beasiswa_icon ?> <?= $d['rekomendasi_beasiswa'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">❌ Tidak</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="detail.php?id=<?= $d['id'] ?>" class="btn btn-info" title="Detail"><i class="fas fa-eye"></i></a>
                                        <a href="edit.php?id=<?= $d['id'] ?>" class="btn btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="hapus.php?id=<?= $d['id'] ?>" class="btn btn-danger" onclick="return confirm('Yakin hapus data <?= $d['nama_siswa'] ?>?')" title="Hapus"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Tombol Hapus Terpilih -->
                <div class="mt-3" id="btnHapusTerpilih" style="display: none;">
                    <button type="button" class="btn btn-warning" onclick="hapusTerpilih()">
                        <i class="fas fa-trash-alt"></i> Hapus Data Terpilih
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Hapus Semua -->
<div class="modal fade" id="modalHapusSemua" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Hapus Semua Data</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus <strong>SEMUA data siswa</strong>?</p>
                <p class="text-danger"><i class="fas fa-warning"></i> Tindakan ini tidak dapat dibatalkan!</p>
                <div class="alert alert-warning">
                    <strong>Data yang akan dihapus:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Semua data siswa</li>
                        <li>Riwayat perhitungan SPK</li>
                        <li>Rekomendasi jurusan & beasiswa</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="hapus_semua.php" class="btn btn-danger" onclick="return confirm('Konfirmasi terakhir: Hapus SEMUA data?')">
                    <i class="fas fa-trash-alt"></i> Ya, Hapus Semua
                </a>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#tabelSiswa').DataTable({
        responsive: true,
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' },
        scrollX: true,
        order: [[9, 'desc']],
        columnDefs: [
            { orderable: false, targets: [0, 13] }
        ]
    });
    
    $('#checkAll').change(function() {
        $('.checkItem').prop('checked', $(this).prop('checked'));
        toggleHapusTerpilih();
    });
    
    $('.checkItem').change(function() {
        toggleHapusTerpilih();
        if($('.checkItem:checked').length == $('.checkItem').length) {
            $('#checkAll').prop('checked', true);
        } else {
            $('#checkAll').prop('checked', false);
        }
    });
    
    function toggleHapusTerpilih() {
        if($('.checkItem:checked').length > 0) {
            $('#btnHapusTerpilih').show();
        } else {
            $('#btnHapusTerpilih').hide();
        }
    }
});

function hapusTerpilih() {
    let ids = [];
    $('.checkItem:checked').each(function() {
        ids.push($(this).val());
    });
    
    if(ids.length === 0) {
        Swal.fire('Peringatan', 'Pilih data yang akan dihapus', 'warning');
        return;
    }
    
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus ${ids.length} data siswa?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if(result.isConfirmed) {
            $.ajax({
                url: 'hapus_terpilih.php',
                type: 'POST',
                data: { ids: ids },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        Swal.fire('Berhasil!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Gagal!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Terjadi kesalahan pada server', 'error');
                }
            });
        }
    });
}
</script>

<style>
/* Style untuk checkbox */
#checkAll, .checkItem {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

/* Tombol group responsif */
.btn-group-sm .btn {
    padding: 0.2rem 0.5rem;
    font-size: 0.75rem;
}

/* Dropdown menu */
.dropdown-menu .dropdown-item i {
    width: 20px;
}

/* Responsive header */
@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 10px;
    }
    .d-flex.justify-content-between .d-flex {
        justify-content: flex-start !important;
        width: 100%;
    }
    .btn {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
    }
}

/* Table responsive */
@media (max-width: 768px) {
    .table td, .table th {
        padding: 0.5rem;
        font-size: 0.75rem;
    }
    .badge {
        font-size: 0.65rem;
        padding: 0.25rem 0.5rem;
    }
}
</style>

<?php include '../includes/footer.php'; ?>