<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include 'config/koneksi.php';

$title    = "Dashboard";
$base_url = "./";

$total_siswa   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM siswa"))['t'] ?? 0;
$total_dkv     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM siswa WHERE rekomendasi_jurusan='DKV'"))['t'] ?? 0;
$total_tkr     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM siswa WHERE rekomendasi_jurusan='TKR'"))['t'] ?? 0;
$rata_rata     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rata_rata) as a FROM siswa"))['a'] ?? 0;

$siswa_layak   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM siswa WHERE rekomendasi_beasiswa='Ya'"))['t'] ?? 0;
$siswa_tidak   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM siswa WHERE rekomendasi_beasiswa='Tidak'"))['t'] ?? 0;
$rata_bea      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(skor_beasiswa) as a FROM siswa WHERE skor_beasiswa>0"))['a'] ?? 0;

$pct_dkv   = $total_siswa > 0 ? round($total_dkv / $total_siswa * 100, 1) : 0;
$pct_tkr   = $total_siswa > 0 ? round($total_tkr / $total_siswa * 100, 1) : 0;
$pct_layak = $total_siswa > 0 ? round($siswa_layak / $total_siswa * 100, 1) : 0;
$pct_tidak = $total_siswa > 0 ? round($siswa_tidak / $total_siswa * 100, 1) : 0;

$chart_labels  = [];
$chart_data    = [];
$chart_jurusan = [];
$q = mysqli_query($conn, "SELECT nama_siswa, rata_rata, rekomendasi_jurusan FROM siswa ORDER BY rata_rata DESC LIMIT 10");
while($r = mysqli_fetch_assoc($q)) {
    $chart_labels[]  = $r['nama_siswa'];
    $chart_data[]    = round($r['rata_rata'], 2);
    $chart_jurusan[] = $r['rekomendasi_jurusan'];
}

$top5 = mysqli_query($conn, "SELECT * FROM siswa ORDER BY rata_rata DESC LIMIT 5");

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<style>
.db-section-label {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #6c757d;
    margin-bottom: .75rem;
}
.stat-grid-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #dee2e6;
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
}
.stat-grid-table td {
    padding: 1rem 1.25rem;
    border-right: 1px solid #dee2e6;
    width: 25%;
    vertical-align: top;
}
.stat-grid-table td:last-child { border-right: none; }
.stat-grid-3 td { width: 33.33%; }
.stat-label { font-size: 12px; color: #6c757d; margin-bottom: 4px; }
.stat-value { font-size: 22px; font-weight: 600; color: #212529; margin-bottom: 2px; }
.stat-sub   { font-size: 11px; color: #adb5bd; }

.db-card {
    border: 1px solid #dee2e6;
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
}
.db-card-head {
    padding: .65rem 1rem;
    border-bottom: 1px solid #dee2e6;
    font-size: 13px;
    font-weight: 600;
    color: #495057;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    gap: 6px;
}
.db-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.db-table thead th {
    padding: 9px 12px;
    font-size: 11px;
    font-weight: 600;
    color: #6c757d;
    text-align: left;
    border-bottom: 1px solid #dee2e6;
    background: #f8f9fa;
    white-space: nowrap;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.db-table tbody td {
    padding: 10px 12px;
    border-bottom: 1px solid #f1f3f5;
    color: #212529;
    vertical-align: middle;
}
.db-table tbody tr:last-child td { border-bottom: none; }
.db-table tbody tr:hover td { background: #f8f9fa; }
.td-muted { color: #6c757d; font-size: 12px; }
.td-num   { text-align: right; font-weight: 600; font-variant-numeric: tabular-nums; }
.td-rank  { font-size: 12px; font-weight: 600; color: #6c757d; }

.db-tag {
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 20px;
    border: 1px solid;
    white-space: nowrap;
}
.tag-dkv  { background: #e7f1ff; color: #1a5fac; border-color: #b6d0f7; }
.tag-tkr  { background: #ffeaea; color: #b91c1c; border-color: #fbbfbf; }
.tag-ya   { background: #e8f5e9; color: #1b6f2e; border-color: #a3d9a5; }
.tag-tidak{ background: #f1f3f5; color: #6c757d; border-color: #dee2e6; }
.tag-sc   { background: #e8f5e9; color: #1b6f2e; border-color: #a3d9a5; }
.tag-co   { background: #e7f1ff; color: #1a5fac; border-color: #b6d0f7; }
.tag-cc   { background: #fff8e1; color: #8a6000; border-color: #f9d74b; }

.mini-bar {
    display: inline-block;
    vertical-align: middle;
    height: 3px;
    width: 48px;
    background: #dee2e6;
    border-radius: 2px;
    margin-left: 6px;
    overflow: hidden;
}
.mini-bar-fill { height: 100%; border-radius: 2px; }

.bar-chart-list  { padding: 1rem; display: flex; flex-direction: column; gap: 10px; }
.bar-chart-row   { display: grid; grid-template-columns: 100px 1fr 36px; align-items: center; gap: 8px; font-size: 12px; }
.bar-chart-name  { text-align: right; color: #6c757d; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.bar-chart-track { height: 18px; background: #f1f3f5; border-radius: 4px; overflow: hidden; }
.bar-chart-fill  { height: 100%; border-radius: 4px; display: flex; align-items: center; padding-left: 6px; font-size: 10px; font-weight: 600; }
.bar-dkv { background: #dde9ff; color: #1a5fac; }
.bar-tkr { background: #ffe0e0; color: #b91c1c; }
.bar-chart-val   { font-size: 12px; font-weight: 600; color: #495057; text-align: right; }

.donut-legend-table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: .75rem; }
.donut-legend-table td { padding: 6px 8px; border: 1px solid #dee2e6; }
.donut-legend-table tr:first-child td {
    background: #f8f9fa;
    font-weight: 600;
    font-size: 11px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: .04em;
}

</style>

<div class="main-content">
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
    <div>
        <h5 class="mb-0 fw-semibold">Dashboard</h5>
        <small class="text-muted">Selamat datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'Administrator') ?></small>
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <!-- Kalender -->
        <div class="card p-2 bg-light border-0" style="min-width: 180px;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-calendar-alt text-primary"></i>
                <span id="liveDate" class="small fw-semibold text-dark">--, -- -- ----</span>
            </div>
        </div>
        <!-- Tombol Hapus -->
        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalHapusSemua">
            <i class="fas fa-trash-alt me-1"></i> Hapus semua data
        </button>
    </div>
</div>

    <!-- Ringkasan siswa -->
    <div class="db-section-label">Ringkasan siswa</div>
    <table class="stat-grid-table mb-4">
        <tr>
            <td>
                <div class="stat-label">Total siswa</div>
                <div class="stat-value"><?= $total_siswa ?></div>
                <div class="stat-sub">Terdaftar dalam sistem</div>
            </td>
            <td>
                <div class="stat-label">Rekomendasi DKV</div>
                <div class="stat-value"><?= $total_dkv ?></div>
                <div class="stat-sub"><?= $pct_dkv ?>% dari total siswa</div>
            </td>
            <td>
                <div class="stat-label">Rekomendasi TKR</div>
                <div class="stat-value"><?= $total_tkr ?></div>
                <div class="stat-sub"><?= $pct_tkr ?>% dari total siswa</div>
            </td>
            <td>
                <div class="stat-label">Rata-rata nilai</div>
                <div class="stat-value"><?= round($rata_rata, 2) ?></div>
                <div class="stat-sub">Dari semua siswa</div>
            </td>
        </tr>
    </table>

    <!-- Statistik beasiswa -->
    <div class="db-section-label">Statistik beasiswa</div>
    <table class="stat-grid-table stat-grid-3 mb-4">
        <tr>
            <td>
                <div class="stat-label">Layak beasiswa</div>
                <div class="stat-value"><?= $siswa_layak ?></div>
                <div class="stat-sub"><?= $pct_layak ?>% dari total siswa</div>
            </td>
            <td>
                <div class="stat-label">Tidak layak</div>
                <div class="stat-value"><?= $siswa_tidak ?></div>
                <div class="stat-sub"><?= $pct_tidak ?>% dari total siswa</div>
            </td>
            <td>
                <div class="stat-label">Rata-rata skor beasiswa</div>
                <div class="stat-value"><?= number_format($rata_bea, 1) ?>%</div>
                <div class="stat-sub">Dari siswa yang dinilai</div>
            </td>
        </tr>
    </table>

    <!-- Grafik -->
    <div class="row g-3 mb-3">
        <div class="col-md-8">
            <div class="db-card h-100">
                <div class="db-card-head">
                    <i class="fas fa-chart-bar fa-sm"></i> 10 siswa dengan nilai tertinggi
                </div>
                <?php if(count($chart_labels) > 0): ?>
                <div class="bar-chart-list">
                    <?php foreach($chart_labels as $i => $nama):
                        $val = $chart_data[$i];
                        $jur = $chart_jurusan[$i] ?? 'DKV';
                        $cls = $jur === 'DKV' ? 'bar-dkv' : 'bar-tkr';
                    ?>
                    <div class="bar-chart-row">
                        <span class="bar-chart-name"><?= htmlspecialchars($nama) ?></span>
                        <div class="bar-chart-track">
                            <div class="bar-chart-fill <?= $cls ?>" style="width:<?= $val ?>%"><?= $jur ?></div>
                        </div>
                        <span class="bar-chart-val"><?= $val ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-5" style="font-size:13px">Belum ada data siswa</div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="db-card h-100">
                <div class="db-card-head">
                    <i class="fas fa-chart-pie fa-sm"></i> Distribusi jurusan
                </div>
                <div class="p-3 d-flex flex-column align-items-center">
                    <?php if($total_dkv + $total_tkr > 0): ?>
                    <canvas id="jurusanChart" width="180" height="180"></canvas>
                    <table class="donut-legend-table">
                        <tr><td>Jurusan</td><td>Jumlah</td><td>Persentase</td></tr>
                        <tr>
                            <td><span class="db-tag tag-dkv">DKV</span></td>
                            <td style="text-align:center;font-weight:600"><?= $total_dkv ?></td>
                            <td style="text-align:center"><?= $pct_dkv ?>%</td>
                        </tr>
                        <tr>
                            <td><span class="db-tag tag-tkr">TKR</span></td>
                            <td style="text-align:center;font-weight:600"><?= $total_tkr ?></td>
                            <td style="text-align:center"><?= $pct_tkr ?>%</td>
                        </tr>
                        <tr>
                            <td style="font-weight:600">Total</td>
                            <td style="text-align:center;font-weight:600"><?= $total_siswa ?></td>
                            <td style="text-align:center">100%</td>
                        </tr>
                    </table>
                    <?php else: ?>
                    <div class="text-center text-muted py-5" style="font-size:13px">Belum ada data jurusan</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 5 Siswa Terbaik -->
    <div class="db-card">
        <div class="db-card-head">
            <i class="fas fa-trophy fa-sm"></i> 5 siswa terbaik
        </div>
        <div class="table-responsive">
            <table class="db-table">
                <thead>
                    <tr>
                        <th style="width:52px">Rank</th>
                        <th>Nama siswa</th>
                        <th>Asal sekolah</th>
                        <th style="text-align:right">Nilai</th>
                        <th>Jurusan</th>
                        <th style="text-align:right">Skor beasiswa</th>
                        <th>Status beasiswa</th>
                        <th>Kecocokan</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $rank    = 1;
                $has_row = false;
                while($row = mysqli_fetch_assoc($top5)):
                    $has_row     = true;
                    $tag_jurusan = $row['rekomendasi_jurusan'] == 'DKV' ? 'tag-dkv' : 'tag-tkr';
                    $tag_bea     = $row['rekomendasi_beasiswa'] == 'Ya'  ? 'tag-ya'  : 'tag-tidak';
                    $tag_kec     = match($row['tingkat_kecocokan'] ?? '') {
                        'Sangat Cocok' => 'tag-sc',
                        'Cocok'        => 'tag-co',
                        default        => 'tag-cc'
                    };
                    $skor      = isset($row['skor_beasiswa']) ? (float)$row['skor_beasiswa'] : 0;
                    $status    = $row['rekomendasi_beasiswa'] ?? 'Tidak';
                    $bar_color = $status == 'Ya' ? '#1a5fac' : '#adb5bd';
                ?>
                <tr>
                    <td>
                        <span class="td-rank">#<?= $rank ?></span>
                        <?= $rank == 1 ? '<i class="fas fa-star text-warning ms-1" style="font-size:11px"></i>' : '' ?>
                    </td>
                    <td><strong><?= htmlspecialchars($row['nama_siswa']) ?></strong></td>
                    <td class="td-muted"><?= htmlspecialchars($row['jurusan_asal']) ?></td>
                    <td class="td-num"><?= round($row['rata_rata'], 2) ?></td>
                    <td><span class="db-tag <?= $tag_jurusan ?>"><?= htmlspecialchars($row['rekomendasi_jurusan'] ?? '-') ?></span></td>
                    <td class="td-num">
                        <?= number_format($skor, 2) ?>%
                        <span class="mini-bar">
                            <span class="mini-bar-fill" style="width:<?= min($skor,100) ?>%;background:<?= $bar_color ?>"></span>
                        </span>
                    </td>
                    <td><span class="db-tag <?= $tag_bea ?>"><?= htmlspecialchars($status) ?></span></td>
                    <td><span class="db-tag <?= $tag_kec ?>"><?= htmlspecialchars($row['tingkat_kecocokan'] ?? '-') ?></span></td>
                </tr>
                <?php $rank++; endwhile; ?>
                <?php if(!$has_row): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4" style="font-size:13px">Belum ada data siswa</td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</div>

<!-- Modal Hapus Semua -->
<div class="modal fade" id="modalHapusSemua" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-semibold">Hapus semua data?</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-2">Tindakan ini akan menghapus data berikut secara permanen:</p>
                <table class="table table-sm table-bordered mb-0" style="font-size:13px">
                    <tbody>
                        <tr><td>Semua data siswa</td></tr>
                        <tr><td>Riwayat perhitungan SPK</td></tr>
                        <tr><td>Rekomendasi jurusan &amp; beasiswa</td></tr>
                        <tr><td>Reset ID auto increment</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Batal</button>
                <a href="siswa/hapus_semua.php?confirm=yes" class="btn btn-sm btn-danger">
                    <i class="fas fa-trash-alt me-1"></i> Ya, hapus semua
                </a>
            </div>
        </div>
    </div>
</div>

<script>
<?php if($total_dkv + $total_tkr > 0): ?>
const ctx2 = document.getElementById('jurusanChart');
if(ctx2) {
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['DKV', 'TKR'],
            datasets: [{
                data: [<?= $total_dkv ?>, <?= $total_tkr ?>],
                backgroundColor: ['#dde9ff', '#ffe0e0'],
                borderColor:     ['#b6d0f7', '#fbbfbf'],
                borderWidth: 1,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: false,
            cutout: '62%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + ctx.label + ': ' + ctx.raw + ' siswa'
                    }
                }
            }
        }
    });
}
<?php endif; ?>
</script>
<script>
// Live Date - Hari, Tanggal Bulan Tahun
function updateDateTime() {
    const now = new Date();
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    
    const dayName = days[now.getDay()];
    const day = now.getDate();
    const month = months[now.getMonth()];
    const year = now.getFullYear();
    
    const dateString = `${dayName}, ${day} ${month} ${year}`;
    document.getElementById('liveDate').innerText = dateString;
}

updateDateTime();
// Update setiap detik (opsional, untuk jam jika ingin ditambahkan nanti)
setInterval(updateDateTime, 1000);
</script>
<?php include 'includes/footer.php'; ?>