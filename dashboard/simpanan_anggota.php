<?php
session_start();
if ($_SESSION['role'] !== 'anggota') header("Location: ../index.php");
require '../config.php';

$user_id = $_SESSION['id'];
$result = mysqli_query($conn, "SELECT * FROM simpanan WHERE user_id = $user_id ORDER BY tanggal DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Simpanan Saya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold text-blue-700 mb-4">💰 Simpanan Saya</h1>
    <a href="anggota.php" class="inline-block mb-4 bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">⬅ Kembali ke Dashboard</a>

    <div class="bg-white p-6 rounded shadow">
      <table class="w-full border text-sm">
        <thead class="bg-gray-100">
          <tr>
            <th class="border px-4 py-2">Tanggal</th>
            <th class="border px-4 py-2">Jumlah</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = mysqli_fetch_assoc($result)): ?>
          <tr class="hover:bg-gray-50">
            <td class="border px-4 py-2"><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
            <td class="border px-4 py-2">Rp <?= number_format($row['jumlah'], 0, ',', '.') ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
