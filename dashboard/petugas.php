<?php
session_start();
if ($_SESSION['role'] !== 'petugas') header("Location: ../index.php");
require '../config.php';

// Statistik
$anggota_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='anggota'");
$total_anggota = mysqli_fetch_assoc($anggota_result)['total'];

$simpanan_result = mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM simpanan");
$total_simpanan = number_format($simpanan_result ? mysqli_fetch_assoc($simpanan_result)['total'] ?? 0 : 0, 2, ',', '.');

$pinjaman_result = mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM pinjaman WHERE status='belum lunas'");
$total_pinjaman = number_format($pinjaman_result ? mysqli_fetch_assoc($pinjaman_result)['total'] ?? 0 : 0, 2, ',', '.');

// Ambil aktivitas terbaru
$aktivitas = [];

// Log simpanan
$simpanan_log = mysqli_query($conn, "
  SELECT s.created_at, u.nama, 'Simpanan' AS jenis, s.jumlah 
  FROM simpanan s 
  JOIN users u ON s.user_id = u.id 
  ORDER BY s.created_at DESC 
  LIMIT 5
");
while ($row = mysqli_fetch_assoc($simpanan_log)) {
  $aktivitas[] = $row;
}

// Log pinjaman
$pinjaman_log = mysqli_query($conn, "
  SELECT p.created_at, u.nama, 'Pinjaman' AS jenis, p.jumlah 
  FROM pinjaman p 
  JOIN users u ON p.user_id = u.id 
  ORDER BY p.created_at DESC 
  LIMIT 5
");
while ($row = mysqli_fetch_assoc($pinjaman_log)) {
  $aktivitas[] = $row;
}

// Gabungkan & urutkan
usort($aktivitas, fn($a, $b) => strtotime($b['created_at']) <=> strtotime($a['created_at']));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Petugas</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
  <!-- Navbar -->
  <nav class="bg-blue-600 text-white p-4 shadow">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
      <h1 class="text-xl font-bold">Koperasi Maju Bersama</h1>
      <ul class="flex space-x-4">
        <li><a href="data_anggota.php" class="hover:underline">Data Anggota</a></li>
        <li><a href="simpanan.php" class="hover:underline">Simpanan</a></li>
        <li><a href="pinjaman.php" class="hover:underline">Pinjaman</a></li>
        <li><a href="laporan.php" class="hover:underline">Laporan</a></li>
      </ul>
      <div class="flex items-center space-x-2">
        <span><?= $_SESSION['nama'] ?> (Petugas)</span>
        <a href="../logout.php" class="bg-white text-blue-600 px-3 py-1 rounded hover:bg-gray-200">Logout</a>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <main class="max-w-7xl mx-auto mt-10 p-4 space-y-6">
    <h2 class="text-2xl font-semibold">Dashboard</h2>

    <!-- Statistik -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white rounded-xl shadow p-6 flex items-center justify-between border-l-4 border-blue-500">
        <div>
          <h3 class="text-sm text-gray-500">Total Anggota</h3>
          <p class="text-2xl font-bold mt-1"><?= $total_anggota ?></p>
        </div>
        <div class="text-3xl text-blue-400">👥</div>
      </div>
      <div class="bg-white rounded-xl shadow p-6 flex items-center justify-between border-l-4 border-green-500">
        <div>
          <h3 class="text-sm text-gray-500">Total Simpanan</h3>
          <p class="text-xl font-bold mt-1">Rp <?= $total_simpanan ?></p>
        </div>
        <div class="text-3xl text-green-400">💰</div>
      </div>
      <div class="bg-white rounded-xl shadow p-6 flex items-center justify-between border-l-4 border-orange-500">
        <div>
          <h3 class="text-sm text-gray-500">Pinjaman Aktif</h3>
          <p class="text-xl font-bold mt-1">Rp <?= $total_pinjaman ?></p>
        </div>
        <div class="text-3xl text-orange-400">📉</div>
      </div>
    </div>

    <!-- Aktivitas -->
    <div class="bg-white rounded-xl shadow p-6">
      <h3 class="text-lg font-semibold mb-4">Aktivitas Terkini</h3>
      <ul class="space-y-3">
        <?php if (count($aktivitas) === 0): ?>
          <li class="text-gray-500 italic">Belum ada aktivitas</li>
        <?php else: ?>
          <?php foreach (array_slice($aktivitas, 0, 10) as $a): ?>
            <li class="flex items-start space-x-3">
              <div class="text-xl <?= $a['jenis'] === 'Simpanan' ? 'text-green-600' : 'text-orange-600' ?>">
                <?= $a['jenis'] === 'Simpanan' ? '💸' : '📝' ?>
              </div>
              <div>
                <p class="font-medium"><?= $a['jenis'] ?> oleh <strong><?= $a['nama'] ?></strong> sebesar Rp <?= number_format($a['jumlah'], 0, ',', '.') ?></p>
                <p class="text-sm text-gray-500"><?= date('d M Y H:i', strtotime($a['created_at'])) ?></p>
              </div>
            </li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>
    </div>
  </main>
</body>
</html>
