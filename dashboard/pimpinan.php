<?php
session_start();
if ($_SESSION['role'] !== 'pimpinan') header("Location: ../index.php");
require '../config.php';

$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$nama_filter = isset($_GET['nama']) ? $_GET['nama'] : '';

$where = "WHERE role='anggota'";
if ($nama_filter) {
  $where .= " AND nama LIKE '%$nama_filter%'";
}

$anggota = mysqli_query($conn, "SELECT id, nama FROM users $where");
$anggota_data = [];
while ($row = mysqli_fetch_assoc($anggota)) {
  $anggota_data[] = $row;
}

$simpanan_data = [];
$simpanan_q = mysqli_query($conn, "SELECT user_id, tanggal, SUM(jumlah) as total FROM simpanan WHERE MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun' GROUP BY user_id");
while ($r = mysqli_fetch_assoc($simpanan_q)) {
  $simpanan_data[$r['user_id']] = ['total' => $r['total'], 'tanggal' => $r['tanggal']];
}

$pinjaman_data = [];
$pinjaman_q = mysqli_query($conn, "SELECT user_id, tanggal, SUM(jumlah) as total, MAX(tanggal) as terakhir FROM pinjaman WHERE MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun' GROUP BY user_id");
while ($r = mysqli_fetch_assoc($pinjaman_q)) {
  $pinjaman_data[$r['user_id']] = ['total' => $r['total'], 'tanggal' => $r['terakhir']];
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Dashboard Pimpinan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-blue-700">📊 Dashboard Pimpinan</h1>
      <a href="../logout.php" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Logout</a>
    </div>

    <form method="GET" class="bg-white p-4 rounded shadow mb-6 flex flex-wrap gap-4 items-center">
      <input type="text" name="nama" placeholder="Cari Nama" value="<?= htmlspecialchars($nama_filter) ?>" class="p-2 border rounded">
      <select name="bulan" class="p-2 border rounded">
        <?php for ($i = 1; $i <= 12; $i++) {
          $val = str_pad($i, 2, '0', STR_PAD_LEFT);
          $sel = $val == $bulan ? 'selected' : '';
          echo "<option value='$val' $sel>" . date('F', mktime(0,0,0,$i,1)) . "</option>";
        } ?>
      </select>
      <select name="tahun" class="p-2 border rounded">
        <?php for ($y = 2022; $y <= date('Y'); $y++) {
          $sel = $y == $tahun ? 'selected' : '';
          echo "<option value='$y' $sel>$y</option>";
        } ?>
      </select>
      <button class="bg-blue-600 text-white px-4 py-2 rounded">Tampilkan</button>
    </form>

    <div class="bg-white p-6 rounded shadow mb-6">
      <canvas id="chart"></canvas>
    </div>

    <div class="bg-white p-6 rounded shadow">
      <h2 class="text-xl font-semibold mb-4">📋 Data Anggota</h2>
      <table class="w-full table-auto text-sm border" id="table">
        <thead class="bg-gray-100">
          <tr>
            <th class="border px-4 py-2">ID</th>
            <th class="border px-4 py-2">Nama</th>
            <th class="border px-4 py-2">Total Simpanan</th>
            <th class="border px-4 py-2">Tanggal Simpan</th>
            <th class="border px-4 py-2">Total Pinjaman</th>
            <th class="border px-4 py-2">Tanggal Pinjam</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($anggota_data as $a): ?>
          <tr class="hover:bg-gray-50">
            <td class="border px-4 py-2"><?= $a['id'] ?></td>
            <td class="border px-4 py-2"><?= $a['nama'] ?></td>
            <td class="border px-4 py-2">Rp <?= number_format($simpanan_data[$a['id']]['total'] ?? 0, 0, ',', '.') ?></td>
            <td class="border px-4 py-2"><?= $simpanan_data[$a['id']]['tanggal'] ?? '-' ?></td>
            <td class="border px-4 py-2">Rp <?= number_format($pinjaman_data[$a['id']]['total'] ?? 0, 0, ',', '.') ?></td>
            <td class="border px-4 py-2"><?= $pinjaman_data[$a['id']]['tanggal'] ?? '-' ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <button onclick="downloadPDF()" class="mt-4 bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Download PDF</button>
    </div>
  </div>

  <script>
    const ctx = document.getElementById('chart');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?= json_encode(array_column($anggota_data, 'nama')) ?>,
        datasets: [
          {
            label: 'Simpanan',
            data: <?= json_encode(array_map(fn($a) => $simpanan_data[$a['id']]['total'] ?? 0, $anggota_data)) ?>,
            backgroundColor: 'rgba(16, 185, 129, 0.6)'
          },
          {
            label: 'Pinjaman',
            data: <?= json_encode(array_map(fn($a) => $pinjaman_data[$a['id']]['total'] ?? 0, $anggota_data)) ?>,
            backgroundColor: 'rgba(234, 179, 8, 0.6)'
          }
        ]
      },
      options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true } }
      }
    });

    function downloadPDF() {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();
      doc.text("Laporan Anggota", 14, 20);
      doc.autoTable({ html: '#table', startY: 30 });
      doc.save('laporan-anggota.pdf');
    }
  </script>
</body>
</html>
