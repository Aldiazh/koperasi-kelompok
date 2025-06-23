<?php
session_start();
if ($_SESSION['role'] !== 'petugas') header("Location: ../index.php");
require '../config.php';

// Tambah pinjaman manual (oleh petugas)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_manual'])) {
  $user_id = $_POST['user_id'];
  $jumlah = $_POST['jumlah'];
  $jaminan = $_POST['jaminan'];
  $angsuran = $_POST['angsuran'];
  $status = $_POST['status'];

  // Cegah input langsung status lunas saat pengajuan
  if ($status === 'lunas') {
    $status = 'pending';
  }

  $query = "INSERT INTO pinjaman (user_id, tanggal, jumlah, jaminan, angsuran, status)
            VALUES ('$user_id', NOW(), '$jumlah', '$jaminan', '$angsuran', '$status')";
  if (mysqli_query($conn, $query)) {
    echo "<script>alert('Pinjaman berhasil ditambahkan'); window.location.href='pinjaman_petugas.php';</script>";
  } else {
    echo "<script>alert('Gagal menambahkan pinjaman');</script>";
  }
}

// Update status pengajuan anggota
if (isset($_POST['update_status'])) {
  $pinjaman_id = $_POST['id'];
  $status = $_POST['status'];

  // Ambil data pinjaman
  $cek = mysqli_query($conn, "SELECT jumlah, status FROM pinjaman WHERE id = $pinjaman_id LIMIT 1");
  $data = mysqli_fetch_assoc($cek);

  // Cegah update langsung ke lunas jika belum pernah disetujui
  if ($data['status'] === 'pending' && $status === 'lunas') {
    echo "<script>alert('Pinjaman belum bisa dilunasi sebelum disetujui.'); window.location.href='pinjaman_petugas.php';</script>";
    exit;
  }

  // Jika disetujui, pastikan masuk ke daftar aktif anggota
  if ($status === 'disetujui') {
    mysqli_query($conn, "UPDATE pinjaman SET status='disetujui' WHERE id=$pinjaman_id");
  } else {
    mysqli_query($conn, "UPDATE pinjaman SET status='$status' WHERE id=$pinjaman_id");
  }

  echo "<script>alert('Status pinjaman diperbarui'); window.location.href='pinjaman_petugas.php';</script>";
}

// Cek dan perbarui status lunas otomatis berdasarkan angsuran
$cek_pinjaman = mysqli_query($conn, "SELECT id, jumlah FROM pinjaman WHERE status='disetujui'");
while ($row = mysqli_fetch_assoc($cek_pinjaman)) {
  $id = $row['id'];
  $jumlah = $row['jumlah'];
  $angsuran = mysqli_query($conn, "SELECT SUM(bayar) as total_bayar FROM angsuran_log WHERE pinjaman_id=$id");
  $total = mysqli_fetch_assoc($angsuran)['total_bayar'] ?? 0;
  if ($total >= $jumlah) {
    mysqli_query($conn, "UPDATE pinjaman SET status='lunas', jumlah=0 WHERE id=$id");
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola Pinjaman</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
<div class="max-w-6xl mx-auto">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-blue-700">💳 Kelola Pinjaman</h1>
    <a href="petugas.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">⬅ Kembali</a>
  </div>

  <!-- Form Tambah Manual -->
  <div class="bg-white p-6 rounded shadow mb-10">
    <h2 class="text-lg font-semibold mb-4">➕ Tambah Pinjaman Manual</h2>
    <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <input type="hidden" name="tambah_manual">
      <div>
        <label class="block text-sm">ID Anggota</label>
        <input type="number" name="user_id" class="w-full border p-2 rounded" required>
      </div>
      <div>
        <label class="block text-sm">Jumlah Pinjaman</label>
        <input type="number" name="jumlah" class="w-full border p-2 rounded" required>
      </div>
      <div>
        <label class="block text-sm">Barang Jaminan</label>
        <input type="text" name="jaminan" class="w-full border p-2 rounded">
      </div>
      <div>
        <label class="block text-sm">Angsuran (bulan)</label>
        <input type="number" name="angsuran" class="w-full border p-2 rounded">
      </div>
      <div>
        <label class="block text-sm">Status</label>
        <select name="status" class="w-full border p-2 rounded">
          <option value="pending">Pending</option>
          <option value="disetujui">Disetujui</option>
          <option value="ditolak">Ditolak</option>
        </select>
      </div>
      <div class="col-span-2 mt-4">
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Simpan Pinjaman</button>
      </div>
    </form>
  </div>

  <!-- Daftar Pengajuan Baru -->
  <div class="bg-white p-6 rounded shadow mb-10">
    <h2 class="text-lg font-semibold mb-4">📨 Pengajuan Baru dari Anggota</h2>
    <table class="w-full text-sm border">
      <thead class="bg-gray-100">
        <tr>
          <th class="border px-3 py-2">Anggota</th>
          <th class="border px-3 py-2">Jumlah</th>
          <th class="border px-3 py-2">Jaminan</th>
          <th class="border px-3 py-2">Angsuran</th>
          <th class="border px-3 py-2">Tanggal</th>
          <th class="border px-3 py-2">Tindakan</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $result = mysqli_query($conn, "SELECT p.*, u.nama FROM pinjaman p JOIN users u ON p.user_id = u.id WHERE p.status='pending' ORDER BY p.tanggal DESC");
        while ($row = mysqli_fetch_assoc($result)) {
          echo "<tr class='hover:bg-yellow-50'>
            <form method='POST'>
              <input type='hidden' name='id' value='{$row['id']}'>
              <td class='border px-3 py-2'>{$row['nama']}<br><span class='text-xs text-gray-500'>#{$row['user_id']}</span></td>
              <td class='border px-3 py-2'>Rp " . number_format($row['jumlah'], 0, ',', '.') . "</td>
              <td class='border px-3 py-2'>{$row['jaminan']}</td>
              <td class='border px-3 py-2'>{$row['angsuran']} bulan</td>
              <td class='border px-3 py-2'>{$row['tanggal']}</td>
              <td class='border px-3 py-2'>
                <select name='status' class='border p-1 rounded'>
                  <option value='pending'" . ($row['status'] === 'pending' ? ' selected' : '') . ">⏳ Pending</option>
                  <option value='disetujui'>✅ Setujui</option>
                  <option value='ditolak'>❌ Tolak</option>
                </select>
                <button name='update_status' class='bg-green-600 text-white px-2 py-1 rounded mt-1 hover:bg-green-700'>Update</button>
              </td>
            </form>
          </tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

  <!-- Semua Pinjaman -->
  <div class="bg-white p-6 rounded shadow">
    <h2 class="text-lg font-semibold mb-4">📂 Riwayat Semua Pinjaman</h2>
    <table class="w-full text-sm border">
      <thead class="bg-gray-100">
        <tr>
          <th class="border px-4 py-2">ID</th>
          <th class="border px-4 py-2">Nama</th>
          <th class="border px-4 py-2">Jumlah</th>
          <th class="border px-4 py-2">Status</th>
          <th class="border px-4 py-2">Tanggal</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $all = mysqli_query($conn, "SELECT p.*, u.nama FROM pinjaman p JOIN users u ON p.user_id = u.id ORDER BY p.tanggal DESC");
        while ($row = mysqli_fetch_assoc($all)) {
          echo "<tr class='hover:bg-gray-50'>
            <td class='border px-3 py-2'>{$row['id']}</td>
            <td class='border px-3 py-2'>{$row['nama']} (#{$row['user_id']})</td>
            <td class='border px-3 py-2'>Rp " . number_format($row['jumlah'], 0, ',', '.') . "</td>
            <td class='border px-3 py-2'>
              " . ($row['status'] === 'pending' ? '⏳ Pending' :
                   ($row['status'] === 'disetujui' ? '✅ Disetujui' :
                   ($row['status'] === 'ditolak' ? '❌ Ditolak' : '✔️ Lunas'))) . "
            </td>
            <td class='border px-3 py-2'>{$row['tanggal']}</td>
          </tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
