// ========================================
// INCLUDES.JS - KHUSUS UNTUK HEADER, SIDEBAR, FOOTER
// ========================================

// Fungsi loading spinner
function showLoading() {
    if ($('.loading').length === 0) {
        $('body').append('<div class="loading"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    }
    $('.loading').addClass('show');
}

function hideLoading() {
    $('.loading').removeClass('show');
}

// Fungsi format angka ke Rupiah
function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(angka);
}

// Fungsi untuk mengecek apakah mobile
function isMobile() {
    return window.innerWidth <= 768;
}

// Fungsi sidebar toggle untuk mobile
function initSidebarToggle() {
    const toggleBtn = document.getElementById('toggleSidebarBtn');
    const sidebar = document.getElementById('mainSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            sidebar?.classList.toggle('show');
            overlay?.classList.toggle('show');
        });
    }
    
    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar?.classList.remove('show');
            this.classList.remove('show');
        });
    }
    
    // Tutup sidebar saat layar di-resize ke desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar?.classList.remove('show');
            overlay?.classList.remove('show');
        }
    });
    
    // Tutup sidebar saat klik link di dalam sidebar (mobile)
    document.querySelectorAll('#mainSidebar a').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                setTimeout(() => {
                    sidebar?.classList.remove('show');
                    overlay?.classList.remove('show');
                }, 150);
            }
        });
    });
}

// Adjust DataTable untuk mobile
function initDataTableResponsive() {
    $(window).on('resize', function() {
        if ($.fn.DataTable) {
            if ($.fn.DataTable.isDataTable('#tabelSiswa')) {
                $('#tabelSiswa').DataTable().responsive.recalc();
            }
            if ($.fn.DataTable.isDataTable('#tabelRiwayat')) {
                $('#tabelRiwayat').DataTable().responsive.recalc();
            }
            if ($.fn.DataTable.isDataTable('#tabelHasil')) {
                $('#tabelHasil').DataTable().responsive.recalc();
            }
        }
    });
}

// Dokumen siap
$(document).ready(function() {
    console.log("Includes JS siap!");
    
    // Auto hide alert after 3 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 3000);
    
    // Fix untuk modal yang tidak bisa di-scroll di mobile
    $('.modal').on('shown.bs.modal', function() {
        $('body').addClass('modal-open');
    });
    
    $('.modal').on('hidden.bs.modal', function() {
        $('body').removeClass('modal-open');
    });
    
    // Inisialisasi sidebar toggle
    initSidebarToggle();
    
    // Inisialisasi DataTable responsive
    initDataTableResponsive();
});