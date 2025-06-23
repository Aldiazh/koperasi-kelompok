<?php
session_start();
if ($_SESSION['role'] !== 'petugas') header("Location: ../index.php");
require '../config.php';

$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Ambil data anggota
$anggota = mysqli_query($conn, "SELECT id, nama FROM users WHERE role='anggota'");
$anggota_data = [];
while ($row = mysqli_fetch_assoc($anggota)) {
  $anggota_data[] = $row;
}

// Ambil data simpanan dengan tanggal & waktu
$simpanan_data = [];
$result = mysqli_query($conn, "SELECT user_id, tanggal, jumlah FROM simpanan 
                               WHERE MONTH(tanggal) = '$bulan' AND YEAR(tanggal) = '$tahun'");
while ($row = mysqli_fetch_assoc($result)) {
  $simpanan_data[] = $row;
}

// Ambil data pinjaman dengan tanggal & waktu
$pinjaman_data = [];
$result = mysqli_query($conn, "SELECT user_id, tanggal, jumlah FROM pinjaman 
                               WHERE MONTH(tanggal) = '$bulan' AND YEAR(tanggal) = '$tahun'");
while ($row = mysqli_fetch_assoc($result)) {
  $pinjaman_data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Koperasi</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
</head>
<body class="bg-gray-100 p-6">
<div class="max-w-7xl mx-auto">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-blue-700">📊 Laporan Koperasi</h1>
    <div>
      <a href="petugas.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 mr-2">⬅ Kembali</a>
      <button onclick="downloadPDF()" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">⬇ Download PDF</button>
    </div>
  </div>

  <!-- Filter -->
  <form method="GET" class="bg-white p-4 rounded shadow mb-6 flex gap-4 items-center flex-wrap">
    <label>Bulan:
      <select name="bulan" class="ml-2 p-2 border rounded">
        <?php for ($i = 1; $i <= 12; $i++): 
          $val = str_pad($i, 2, '0', STR_PAD_LEFT);
          $selected = ($val == $bulan) ? "selected" : ""; ?>
          <option value="<?= $val ?>" <?= $selected ?>><?= date('F', mktime(0,0,0,$i,1)) ?></option>
        <?php endfor; ?>
      </select>
    </label>
    <label>Tahun:
      <select name="tahun" class="ml-2 p-2 border rounded">
        <?php for ($y = 2022; $y <= date('Y'); $y++): ?>
          <option value="<?= $y ?>" <?= $y == $tahun ? 'selected' : '' ?>><?= $y ?></option>
        <?php endfor; ?>
      </select>
    </label>
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Tampilkan</button>
  </form>

  <!-- Ringkasan -->
  <div class="bg-white p-6 rounded shadow" id="laporanTable">
    <h2 class="text-xl font-semibold mb-2">📋 Detail Transaksi</h2>
    <p class="text-sm text-gray-500 mb-4">Periode: <?= date('F Y', mktime(0, 0, 0, $bulan, 1)) ?> • Diambil: <?= date('d-m-Y H:i:s') ?></p>

    <h3 class="text-lg font-bold mb-2">💰 Simpanan</h3>
    <table class="w-full text-sm border mb-6" id="simpananTable">
      <thead class="bg-gray-100">
        <tr>
          <th class="border px-3 py-2">Anggota</th>
          <th class="border px-3 py-2">Tanggal</th>
          <th class="border px-3 py-2">Jumlah</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($simpanan_data as $s): 
          $nama = array_filter($anggota_data, fn($a) => $a['id'] == $s['user_id']);
          $nama = reset($nama)['nama'] ?? 'Tidak Diketahui';
        ?>
        <tr class="hover:bg-gray-50">
          <td class="border px-3 py-2"><?= $nama ?></td>
          <td class="border px-3 py-2"><?= date('d-m-Y H:i', strtotime($s['tanggal'])) ?></td>
          <td class="border px-3 py-2 text-right">Rp <?= number_format($s['jumlah'], 0, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h3 class="text-lg font-bold mb-2">💳 Pinjaman</h3>
    <table class="w-full text-sm border" id="pinjamanTable">
      <thead class="bg-gray-100">
        <tr>
          <th class="border px-3 py-2">Anggota</th>
          <th class="border px-3 py-2">Tanggal</th>
          <th class="border px-3 py-2">Jumlah</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pinjaman_data as $p): 
          $nama = array_filter($anggota_data, fn($a) => $a['id'] == $p['user_id']);
          $nama = reset($nama)['nama'] ?? 'Tidak Diketahui';
        ?>
        <tr class="hover:bg-gray-50">
          <td class="border px-3 py-2"><?= $nama ?></td>
          <td class="border px-3 py-2"><?= date('d-m-Y H:i', strtotime($p['tanggal'])) ?></td>
          <td class="border px-3 py-2 text-right">Rp <?= number_format($p['jumlah'], 0, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Export to PDF -->
<script>
function downloadPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();
  doc.setFontSize(16);
  doc.text("Laporan Koperasi", 14, 20);
  doc.setFontSize(10);
  doc.text("Tanggal: <?= date('d-m-Y H:i:s') ?>", 14, 27);
  doc.autoTable({ html: '#simpananTable', startY: 35, theme: 'grid', headStyles: { fillColor: [34, 197, 94] } });
  doc.autoTable({ html: '#pinjamanTable', startY: doc.lastAutoTable.finalY + 10, theme: 'grid', headStyles: { fillColor: [234, 179, 8] } });
  doc.save('laporan_koperasi.pdf');
}
</script>
</body>
</html>
