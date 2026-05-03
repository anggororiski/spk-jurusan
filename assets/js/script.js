// Custom JavaScript untuk SPK Jurusan

$(document).ready(function () {
  // Auto close alert after 3 seconds
  setTimeout(function () {
    $(".alert").fadeOut("slow");
  }, 3000);

  // Confirmation for delete actions
  $(".btn-delete").on("click", function (e) {
    if (!confirm("Apakah Anda yakin ingin menghapus data ini?")) {
      e.preventDefault();
    }
  });

  // Format currency input
  $(".currency").on("input", function () {
    let value = $(this).val().replace(/\D/g, "");
    if (value) {
      $(this).val(new Intl.NumberFormat("id-ID").format(value));
    }
  });
});

// Fungsi untuk menampilkan modal detail
function showDetail(id, url) {
  $.ajax({
    url: url + "?id=" + id,
    type: "GET",
    success: function (data) {
      $("#modalDetail .modal-body").html(data);
      $("#modalDetail").modal("show");
    },
  });
}

// Fungsi untuk export data
function exportData(type) {
  window.location.href = "export.php?type=" + type;
}

// Fungsi untuk print
function printPage() {
  window.print();
}
