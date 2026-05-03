<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$title = "Import Excel";
$base_url = "../";
include '../config/koneksi.php';
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-file-excel me-2"></i>Import Data dari Excel</h3>
            <div>
                <a href="template_excel.php" class="btn btn-info me-2" target="_blank">
                    <i class="fas fa-download"></i> Download Template
                </a>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Upload File Excel</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Petunjuk:</strong>
                    <ol class="mb-0 mt-2">
                        <li>Download template Excel terlebih dahulu (klik tombol Download Template)</li>
                        <li>Isi data sesuai format template (Nama, Asal Sekolah, Kelas, JK, Penghasilan, MTK, B.Indo, B.Ing, Disiplin, Tanggung Jawab, Sikap, Komunikasi)</li>
                        <li>Pastikan tidak ada baris kosong di antara data</li>
                        <li>Upload file Excel yang sudah diisi</li>
                        <li>Sistem akan menghitung rata-rata dan standar deviasi otomatis</li>
                    </ol>
                </div>
                
                <form action="import_excel.php" method="POST" enctype="multipart/form-data" id="formImport">
                    <div class="mb-3">
                        <label class="form-label">Pilih File Excel</label>
                        <input type="file" class="form-control" name="file_excel" accept=".xls,.xlsx" required>
                        <small class="text-muted">Format: .xls atau .xlsx (Maks 2MB)</small>
                    </div>
                    
                    <div class="mb-3">
                        <div id="previewArea" class="border rounded p-3 bg-light" style="min-height: 150px;">
                            <p class="text-muted text-center mb-0">Preview data akan muncul setelah file dipilih</p>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success" id="btnImport">
                        <i class="fas fa-upload me-2"></i>Import Data
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- SheetJS untuk preview Excel -->
<script src="https://cdn.sheetjs.com/xlsx-0.20.2/package/dist/xlsx.full.min.js"></script>
<script>
document.querySelector('input[name="file_excel"]').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if(!file) return;
    
    const reader = new FileReader();
    
    reader.onload = function(e) {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, {type: 'array'});
        const sheetName = workbook.SheetNames[0];
        const worksheet = workbook.Sheets[sheetName];
        const jsonData = XLSX.utils.sheet_to_json(worksheet, {header: 1, defval: ""});
        
        let html = '<div class="table-responsive" style="max-height: 300px; overflow-y: auto;">';
        html += '<table class="table table-sm table-bordered">';
        
        if(jsonData.length > 0) {
            // Header
            html += '<thead class="table-light"><tr>';
            for(let i = 0; i < jsonData[0].length; i++) {
                html += `<th>${jsonData[0][i] || 'Kolom ' + (i+1)}</th>`;
            }
            html += '</tr></thead><tbody>';
            
            // Data (max 10 baris untuk preview)
            for(let i = 1; i < Math.min(jsonData.length, 11); i++) {
                html += '<tr>';
                for(let j = 0; j < jsonData[0].length; j++) {
                    let cellValue = jsonData[i][j] || '-';
                    html += `<td>${cellValue}</td>`;
                }
                html += '</tr>';
            }
            
            if(jsonData.length > 11) {
                html += `<tr><td colspan="${jsonData[0].length}" class="text-center text-muted">
                            ... dan ${jsonData.length - 11} baris lainnya
                         </td></tr>`;
            }
        } else {
            html += '<tr><td class="text-center">Tidak ada data</td></tr>';
        }
        
        html += '</tbody></table></div>';
        
        // Info jumlah data
        html += `<div class="mt-2 text-info">
                    <i class="fas fa-info-circle"></i> Ditemukan ${jsonData.length - 1} baris data (tidak termasuk header)
                </div>`;
        
        document.getElementById('previewArea').innerHTML = html;
    };
    
    reader.readAsArrayBuffer(file);
});
</script>

<?php include '../includes/footer.php'; ?>