<?php
session_start();
if ($_SESSION['role'] !== 'petugas') header("Location: ../index.php");
require '../config.php';

// Ambil semua anggota
$anggota_q = mysqli_query($conn, "SELECT id, nama FROM users WHERE role='anggota'");

// Proses simpan manual
if (isset($_POST['simpan'])) {
  $user_id = $_POST['user_id'];
  $jumlah = $_POST['jumlah'];
  $tanggal = date('Y-m-d');
  mysqli_query($conn, "INSERT INTO simpanan (user_id, jumlah, tanggal, status) VALUES ($user_id, $jumlah, '$tanggal', 'disetujui')");
  echo "<script>alert('Simpanan berhasil ditambahkan dan langsung disetujui'); window.location.href='simpanan.php';</script>";
}

// Proses update status
if (isset($_POST['update_status'])) {
  $id = $_POST['id'];
  $status = $_POST['status'];
  mysqli_query($conn, "UPDATE simpanan SET status='$status' WHERE id=$id");
  echo "<script>alert('Status simpanan diperbarui'); window.location.href='simpanan.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Simpanan</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
<div class="max-w-6xl mx-auto">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-blue-700">💰 Simpanan Anggota</h1>
    <a href="petugas.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">⬅ Kembali</a>
  </div>

  <!-- Form Simpan Manual -->
  <div class="bg-white p-6 rounded shadow mb-8">
    <h2 class="text-lg font-semibold mb-4">➕ Input Simpanan Manual</h2>
    <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm mb-1">Anggota</label>
        <select name="user_id" class="w-full border p-2 rounded" required>
          <option value="">Pilih anggota</option>
          <?php while ($a = mysqli_fetch_assoc($anggota_q)) {
            echo "<option value='{$a['id']}'>{$a['nama']}</option>";
          } ?>
        </select>
      </div>
      <div>
        <label class="block text-sm mb-1">Jumlah (Rp)</label>
        <input type="number" name="jumlah" class="w-full border p-2 rounded" required>
      </div>
      <div class="flex items-end">
        <button name="simpan" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Simpan</button>
      </div>
    </form>
  </div>

  <!-- Tabel Simpanan -->
  <div class="bg-white p-6 rounded shadow">
    <h2 class="text-lg font-semibold mb-4">📋 Riwayat Simpanan</h2>
    <table class="w-full table-auto text-sm border">
      <thead class="bg-gray-100">
        <tr>
          <th class="border px-2 py-2">Tanggal</th>
          <th class="border px-2 py-2">Nama</th>
          <th class="border px-2 py-2">Jumlah</th>
          <th class="border px-2 py-2">Status</th>
          <th class="border px-2 py-2">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $data = mysqli_query($conn, "SELECT s.*, u.nama FROM simpanan s JOIN users u ON s.user_id = u.id ORDER BY s.tanggal DESC");
        while ($r = mysqli_fetch_assoc($data)) {
          echo "<tr class='hover:bg-gray-50'>
            <form method='POST'>
              <input type='hidden' name='id' value='{$r['id']}'>
              <td class='border px-2 py-1'>{$r['tanggal']}</td>
              <td class='border px-2 py-1'>{$r['nama']}</td>
              <td class='border px-2 py-1'>Rp " . number_format($r['jumlah'], 0, ',', '.') . "</td>
              <td class='border px-2 py-1'>
                <select name='status' class='border p-1 rounded'>
                  <option value='pending' " . ($r['status'] == 'pending' ? 'selected' : '') . ">⏳ Pending</option>
                  <option value='disetujui' " . ($r['status'] == 'disetujui' ? 'selected' : '') . ">✅ Disetujui</option>
                  <option value='ditolak' " . ($r['status'] == 'ditolak' ? 'selected' : '') . ">❌ Ditolak</option>
                </select>
              </td>
              <td class='border px-2 py-1'>
                <button name='update_status' class='bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700'>Update</button>
              </td>
            </form>
          </tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
