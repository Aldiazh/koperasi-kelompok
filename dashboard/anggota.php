<?php
session_start();
if ($_SESSION['role'] !== 'anggota') header("Location: ../index.php");
require '../config.php';

$user_id = $_SESSION['id'];
$nama = $_SESSION['nama'];

// Ambil total simpanan
$simpanan_q = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM simpanan WHERE user_id = $user_id AND status = 'disetujui'");
$total_simpanan = mysqli_fetch_assoc($simpanan_q)['total'] ?? 0;

// Ambil total pinjaman aktif
$pinjaman_q = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM pinjaman WHERE user_id = $user_id AND (status='disetujui' OR status='belum lunas')");
$total_pinjaman = mysqli_fetch_assoc($pinjaman_q)['total'] ?? 0;

// Ajukan pinjaman dengan status pending
if (isset($_POST['ajukan'])) {
  $jumlah = $_POST['jumlah'];
  $jaminan = $_POST['jaminan'];
  $angsuran = $_POST['angsuran'];
  mysqli_query($conn, "INSERT INTO pinjaman (user_id, tanggal, jumlah, jaminan, angsuran, status) VALUES ($user_id, NOW(), $jumlah, '$jaminan', $angsuran, 'pending')");
  echo "<script>alert('Pengajuan pinjaman telah dikirim dan menunggu persetujuan petugas'); window.location='anggota.php';</script>";
}

// Bayar angsuran (dengan opsi potong simpanan)
if (isset($_POST['bayar_angsuran'])) {
  $bayar = $_POST['bayar'];
  $pinjaman_id = $_POST['pinjaman_id'];
  $metode = $_POST['metode_bayar'] ?? 'tunai';

  $pinjaman = mysqli_query($conn, "SELECT * FROM pinjaman WHERE id=$pinjaman_id AND user_id=$user_id LIMIT 1");
  if ($p = mysqli_fetch_assoc($pinjaman)) {
    if ($bayar > $p['jumlah']) {
      echo "<script>alert('Jumlah pembayaran melebihi sisa pinjaman'); window.location='anggota.php';</script>";
      exit;
    }
    $sisa = $p['jumlah'] - $bayar;
    $status = $sisa <= 0 ? "lunas" : "disetujui";

    if ($metode === 'simpanan') {
      $simpanan_q = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM simpanan WHERE user_id = $user_id AND status = 'disetujui'");
      $saldo_simpanan = mysqli_fetch_assoc($simpanan_q)['total'] ?? 0;
      if ($saldo_simpanan < $bayar) {
        echo "<script>alert('Saldo simpanan tidak cukup!'); window.location='anggota.php';</script>";
        exit;
      }
      mysqli_query($conn, "INSERT INTO simpanan (user_id, tanggal, jumlah, metode, status) VALUES ($user_id, NOW(), -$bayar, 'Potong Angsuran', 'disetujui')");
    }

    mysqli_query($conn, "UPDATE pinjaman SET jumlah=$sisa, status='$status' WHERE id={$p['id']}");
    mysqli_query($conn, "INSERT INTO angsuran_log (user_id, pinjaman_id, bayar, tanggal, metode) VALUES ($user_id, {$p['id']}, $bayar, NOW(), '$metode')");
    echo "<script>alert('Pembayaran berhasil'); window.location='anggota.php';</script>";
  }
}

// Input Simpanan
if (isset($_POST['simpan_simpanan'])) {
  $nominal = $_POST['jumlah_simpanan'];
  mysqli_query($conn, "INSERT INTO simpanan (user_id, tanggal, jumlah, metode, status) VALUES ($user_id, NOW(), $nominal, 'Manual Input', 'pending')");
  echo "<script>alert('Permintaan simpanan berhasil dikirim, menunggu persetujuan petugas'); window.location='anggota.php';</script>";
}

$riwayat_pinjaman = mysqli_query($conn, "SELECT * FROM pinjaman WHERE user_id = $user_id ORDER BY tanggal DESC");
$riwayat_angsuran = mysqli_query($conn, "SELECT * FROM angsuran_log WHERE user_id = $user_id ORDER BY tanggal DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Anggota</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-100 to-white min-h-screen p-6">
  <div class="max-w-6xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-3xl font-bold text-blue-800 animate-bounce">👋 Selamat Datang, <?= $nama ?>!</h1>
      <a href="../logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">Logout</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <a href="simpanan_anggota.php" class="card bg-white shadow-md rounded-xl p-6 hover:shadow-xl">
        <h3 class="text-lg font-semibold text-gray-700 mb-2">💰 Total Simpanan</h3>
        <p class="text-green-600 text-2xl font-bold">Rp <?= number_format($total_simpanan, 0, ',', '.') ?></p>
      </a>

      <div class="card bg-white shadow-md rounded-xl p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">📥 Input Simpanan</h3>
        <form method="POST" class="space-y-2">
          <input type="number" name="jumlah_simpanan" class="w-full border p-2 rounded" placeholder="Jumlah Simpanan" required>
          <button name="simpan_simpanan" class="w-full bg-green-600 text-white p-2 rounded hover:bg-green-700 transition">Simpan</button>
        </form>
      </div>

      <div class="card bg-white shadow-md rounded-xl p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">💳 Ajukan Pinjaman</h3>
        <form method="POST" class="space-y-2">
          <input type="number" name="jumlah" class="w-full border p-2 rounded" placeholder="Jumlah Pinjaman" required>
          <input type="text" name="jaminan" class="w-full border p-2 rounded" placeholder="Barang Jaminan" required>
          <input type="number" name="angsuran" class="w-full border p-2 rounded" placeholder="Angsuran / Bulan" required>
          <button name="ajukan" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700 transition">Ajukan</button>
        </form>
        <p class="text-xs text-gray-500 mt-2">Pengajuan menunggu persetujuan petugas.</p>
      </div>

      <!-- 📉 Pinjaman Aktif & Pembayaran -->
      <div class="card bg-white shadow-md rounded-xl p-6 col-span-1 md:col-span-2 lg:col-span-2">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">📉 Pinjaman Aktif & Bayar Angsuran</h3>
        <p class="text-orange-600 text-2xl font-bold mb-2">Total: Rp <?= number_format($total_pinjaman, 0, ',', '.') ?></p>
        <?php
          $pinjaman_aktif = mysqli_query($conn, "SELECT * FROM pinjaman WHERE user_id = $user_id AND status='disetujui' AND jumlah > 0 ORDER BY tanggal ASC");
          if (mysqli_num_rows($pinjaman_aktif) > 0):
            while ($p = mysqli_fetch_assoc($pinjaman_aktif)):
        ?>
          <form method="POST" class="space-y-2 border-t pt-4 mb-4">
            <input type="hidden" name="pinjaman_id" value="<?= $p['id'] ?>">
            <p class="text-sm text-gray-600">#<?= $p['id'] ?> | Tanggal: <?= date('d-m-Y', strtotime($p['tanggal'])) ?></p>
            <p class="text-sm text-gray-700">Sisa: Rp <?= number_format($p['jumlah'], 0, ',', '.') ?> | Angsuran: Rp <?= number_format($p['angsuran'], 0, ',', '.') ?></p>
            <input type="number" name="bayar" class="w-full border p-2 rounded" placeholder="Jumlah Pembayaran" min="1" max="<?= $p['jumlah'] ?>" required>
            <select name="metode_bayar" class="w-full border p-2 rounded" required>
              <option value="tunai">Tunai</option>
              <option value="simpanan">Potong dari Simpanan</option>
            </select>
            <button name="bayar_angsuran" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Bayar Sekarang</button>
          </form>
        <?php
            endwhile;
          else:
        ?>
          <p class="text-gray-500">Tidak ada pinjaman aktif saat ini.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Riwayat Pengajuan Pinjaman -->
    <div class="bg-white rounded-xl shadow p-6">
      <h3 class="text-lg font-semibold mb-4">📄 Riwayat Pengajuan Pinjaman</h3>
      <table class="w-full table-auto border text-sm">
        <thead class="bg-gray-100">
          <tr>
            <th class="border px-3 py-2">Tanggal</th>
            <th class="border px-3 py-2">Jumlah</th>
            <th class="border px-3 py-2">Jaminan</th>
            <th class="border px-3 py-2">Angsuran</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = mysqli_fetch_assoc($riwayat_pinjaman)): ?>
            <tr class="hover:bg-gray-50">
              <td class="border px-3 py-2"><?= date('d-m-Y H:i', strtotime($row['tanggal'])) ?></td>
              <td class="border px-3 py-2">Rp <?= number_format($row['jumlah'], 0, ',', '.') ?></td>
              <td class="border px-3 py-2"><?= $row['jaminan'] ?></td>
              <td class="border px-3 py-2">Rp <?= number_format($row['angsuran'], 0, ',', '.') ?></td>
              <td class="border px-3 py-2">
                <?php
                  if ($row['status'] === 'pending') echo "<span class='text-yellow-600 font-semibold'>⏳ Menunggu</span>";
                  elseif ($row['status'] === 'disetujui') echo "<span class='text-green-600 font-semibold'>✅ Disetujui</span>";
                  elseif ($row['status'] === 'ditolak') echo "<span class='text-red-600 font-semibold'>❌ Ditolak</span>";
                  elseif ($row['status'] === 'lunas') echo "<span class='text-gray-600 font-semibold'>✔️ Lunas</span>";
                ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
