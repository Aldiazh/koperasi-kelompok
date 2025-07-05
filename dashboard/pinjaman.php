<?php
session_start();
if ($_SESSION['role'] !== 'petugas') header("Location: ../index.php");
require '../config.php';

// Tambah pinjaman manual (oleh petugas) - selalu masuk sebagai pending
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_manual'])) {
  $user_id = $_POST['user_id'];
  $jumlah = $_POST['jumlah'];
  $jaminan = $_POST['jaminan'];
  $angsuran = $_POST['angsuran'];

  // Cek apakah user_id valid di tabel users
  $cek_user = mysqli_query($conn, "SELECT id FROM users WHERE id = $user_id");
  if (mysqli_num_rows($cek_user) === 0) {
    echo "<script>alert('ID Anggota tidak ditemukan. Pastikan ID sudah benar dan terdaftar.'); window.location.href='pinjaman.php';</script>";
    exit;
  }

  // Semua input manual selalu masuk sebagai pending untuk validasi admin
  $status = 'pending';

  $query = "INSERT INTO pinjaman (user_id, tanggal, jumlah, jaminan, angsuran, status)
            VALUES ('$user_id', NOW(), '$jumlah', '$jaminan', '$angsuran', '$status')";
  if (mysqli_query($conn, $query)) {
    echo "<script>alert('Pinjaman berhasil ditambahkan'); window.location.href='pinjaman.php';</script>";
  } else {
    echo "<script>alert('Gagal menambahkan pinjaman. Silakan periksa kembali data.');</script>";
  }
}

// Edit data pengajuan yang masih pending
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_pengajuan'])) {
  $pinjaman_id = $_POST['id'];
  $user_id = $_POST['user_id'];
  $jumlah = $_POST['jumlah'];
  $jaminan = $_POST['jaminan'];
  $angsuran = $_POST['angsuran'];

  // Cek apakah user_id valid di tabel users
  // $cek_user = mysqli_query($conn, "SELECT id FROM users WHERE id = $user_id");
  // if (mysqli_num_rows($cek_user) === 0) {
  //   echo "<script>alert('ID Anggota tidak ditemukan. Pastikan ID sudah benar dan terdaftar.'); window.location.href='pinjaman.php';</script>";
  //   exit;
  // }

  // Hanya bisa edit jika status masih pending
  $cek_status = mysqli_query($conn, "SELECT status FROM pinjaman WHERE id = $pinjaman_id");
  $data_status = mysqli_fetch_assoc($cek_status);
  
  if ($data_status['status'] !== 'pending') {
    echo "<script>alert('Hanya pengajuan dengan status pending yang bisa diedit'); window.location.href='pinjaman.php';</script>";
    exit;
  }

  $query = "UPDATE pinjaman SET user_id='$user_id', jumlah='$jumlah', jaminan='$jaminan', angsuran='$angsuran' WHERE id='$pinjaman_id'";
  if (mysqli_query($conn, $query)) {
    echo "<script>alert('Data pengajuan berhasil diperbarui'); window.location.href='pinjaman.php';</script>";
  } else {
    echo "<script>alert('Gagal memperbarui data pengajuan');</script>";
  }
}

// Hapus pengajuan yang masih pending
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['hapus_pengajuan'])) {
  $pinjaman_id = $_POST['id'];
  
  // Hanya bisa hapus jika status masih pending
  $cek_status = mysqli_query($conn, "SELECT status FROM pinjaman WHERE id = $pinjaman_id");
  $data_status = mysqli_fetch_assoc($cek_status);
  
  if ($data_status['status'] !== 'pending') {
    echo "<script>alert('Hanya pengajuan dengan status pending yang bisa dihapus'); window.location.href='pinjaman.php';</script>";
    exit;
  }

  $query = "DELETE FROM pinjaman WHERE id='$pinjaman_id'";
  if (mysqli_query($conn, $query)) {
    echo "<script>alert('Pengajuan berhasil dihapus'); window.location.href='pinjaman.php';</script>";
  } else {
    echo "<script>alert('Gagal menghapus pengajuan');</script>";
  }
}

// Update status pengajuan anggota (validasi admin)
if (isset($_POST['update_status'])) {
  $pinjaman_id = $_POST['id'];
  $status = $_POST['status'];

  // Ambil data pinjaman
  $cek = mysqli_query($conn, "SELECT jumlah, status FROM pinjaman WHERE id = $pinjaman_id LIMIT 1");
  $data = mysqli_fetch_assoc($cek);

  // Cegah update langsung ke lunas jika belum pernah disetujui
  if ($data['status'] === 'pending') {
    if ($status === 'lunas') {
      echo "<script>alert('Pinjaman belum bisa dilunasi sebelum disetujui.'); window.location.href='pinjaman.php';</script>";
      exit;
    }
    // Hanya boleh update ke disetujui, ditolak, atau pending
    if ($status !== 'disetujui' && $status !== 'ditolak' && $status !== 'pending') {
      echo "<script>alert('Status tidak valid untuk pengajuan baru.'); window.location.href='pinjaman.php';</script>";
      exit;
    }
  }

  // Update status
  mysqli_query($conn, "UPDATE pinjaman SET status='$status' WHERE id=$pinjaman_id");
  
  $status_msg = ($status === 'disetujui') ? 'disetujui dan masuk ke riwayat aktif' : 'diperbarui';
  echo "<script>alert('Status pinjaman $status_msg'); window.location.href='pinjaman.php';</script>";
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
    <p class="text-sm text-gray-600 mb-4">* Data yang ditambahkan akan masuk ke pengajuan untuk validasi admin</p>
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
      <div class="col-span-2 mt-4">
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Tambah ke Pengajuan</button>
      </div>
    </form>
  </div>

  <!-- Riwayat Pinjaman Selesai (Lunas & Ditolak) -->
  <div class="bg-white p-6 rounded shadow">
    <h2 class="text-lg font-semibold mb-4">📂 Daftar Peminjaman</h2>
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
        // Tampilkan pinjaman yang sudah lunas atau ditolak
$riwayat = mysqli_query($conn, "SELECT p.*, u.nama FROM pinjaman p JOIN users u ON p.user_id = u.id ORDER BY p.tanggal DESC");
$status_icon = [
  'pending' => '⏳ Pending',
  'disetujui' => '✅ Disetujui',
  'lunas' => '✔️ Lunas',
  'ditolak' => '❌ Ditolak'
];

while ($row = mysqli_fetch_assoc($riwayat)) {
  $status_label = $status_icon[$row['status']] ?? ucfirst($row['status']);
  echo "<tr class='hover:bg-gray-50'>
    <td class='border px-3 py-2'>{$row['id']}</td>
    <td class='border px-3 py-2'>{$row['nama']} (#{$row['user_id']})</td>
    <td class='border px-3 py-2'>Rp " . number_format($row['jumlah'], 0, ',', '.') . "</td>
    <td class='border px-3 py-2'>{$status_label}</td>
    <td class='border px-3 py-2'>{$row['tanggal']}</td>
  </tr>";
}
