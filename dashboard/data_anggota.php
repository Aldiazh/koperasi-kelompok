<?php
session_start();
if ($_SESSION['role'] !== 'petugas') header("Location: ../index.php");
require '../config.php';

// Tambah anggota baru
if (isset($_POST['add'])) {
  $nama = $_POST['nama'];
  $username = $_POST['username'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $nik = $_POST['nik'];
  $alamat = $_POST['alamat'];
  $alamat_tinggal = $_POST['alamat_tinggal'];
  $agama = $_POST['agama'];
  $jenis_kelamin = $_POST['jenis_kelamin'];
  $status = $_POST['status'];
  $penghasilan = $_POST['penghasilan'];

  $query = "INSERT INTO users (nama, username, password, role) VALUES ('$nama', '$username', '$password', 'anggota')";
  if (mysqli_query($conn, $query)) {
    $user_id = mysqli_insert_id($conn);
    mysqli_query($conn, "INSERT INTO anggota_detail (user_id, nama, nik, alamat, alamat_tinggal, agama, jenis_kelamin, status, penghasilan)
      VALUES ('$user_id', '$nama', '$nik', '$alamat', '$alamat_tinggal', '$agama', '$jenis_kelamin', '$status', '$penghasilan')");
    echo "<script>alert('Anggota berhasil ditambahkan'); location.href='data_anggota.php';</script>";
  }
}

// Hapus anggota
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  mysqli_query($conn, "DELETE FROM users WHERE id=$id AND role='anggota'");
  mysqli_query($conn, "DELETE FROM anggota_detail WHERE user_id=$id");
  echo "<script>location.href='data_anggota.php';</script>";
}

// Reset password
if (isset($_POST['reset'])) {
  $id = $_POST['id'];
  $new = password_hash('123456', PASSWORD_DEFAULT);
  mysqli_query($conn, "UPDATE users SET password='$new' WHERE id=$id");
  echo "<script>alert('Password direset ke 123456'); location.href='data_anggota.php';</script>";
}

// Edit akun + data diri
if (isset($_POST['edit'])) {
  $id = $_POST['id'];
  $nama = $_POST['nama'];
  $username = $_POST['username'];
  $nik = $_POST['nik'];
  $alamat = $_POST['alamat'];
  $alamat_tinggal = $_POST['alamat_tinggal'];
  $agama = $_POST['agama'];
  $jenis_kelamin = $_POST['jenis_kelamin'];
  $status = $_POST['status'];
  $penghasilan = $_POST['penghasilan'];

  mysqli_query($conn, "UPDATE users SET nama='$nama', username='$username' WHERE id=$id");
  mysqli_query($conn, "UPDATE anggota_detail SET nama='$nama', nik='$nik', alamat='$alamat', alamat_tinggal='$alamat_tinggal',
    agama='$agama', jenis_kelamin='$jenis_kelamin', status='$status', penghasilan='$penghasilan' WHERE user_id=$id");

  echo "<script>alert('Data berhasil diupdate'); location.href='data_anggota.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Anggota</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-blue-700">📋 Data Anggota</h1>
      <a href="petugas.php" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">⬅ Kembali</a>
    </div>

    <!-- Form Tambah -->
    <div class="bg-white p-6 rounded shadow mb-10">
      <h2 class="text-lg font-semibold mb-4">➕ Tambah Anggota</h2>
      <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input type="hidden" name="add">
        <input name="nama" class="border p-2 rounded" placeholder="Nama" required>
        <input name="username" class="border p-2 rounded" placeholder="Username" required>
        <input type="password" name="password" class="border p-2 rounded" placeholder="Password" required>
        <input name="nik" class="border p-2 rounded" placeholder="NIK" required>
        <input name="alamat" class="border p-2 rounded" placeholder="Alamat KTP">
        <input name="alamat_tinggal" class="border p-2 rounded" placeholder="Alamat Tinggal">
        <input name="agama" class="border p-2 rounded" placeholder="Agama">
        <select name="jenis_kelamin" class="border p-2 rounded">
          <option value="Laki-laki">Laki-laki</option>
          <option value="Perempuan">Perempuan</option>
        </select>
        <input name="status" class="border p-2 rounded" placeholder="Status">
        <input type="number" name="penghasilan" class="border p-2 rounded" placeholder="Penghasilan">
        <div class="col-span-2">
          <button class="bg-blue-600 text-white px-6 py-2 mt-2 rounded hover:bg-blue-700">Simpan</button>
        </div>
      </form>
    </div>

    <!-- Tabel Anggota -->
    <div class="bg-white p-6 rounded shadow overflow-x-auto">
      <h2 class="text-lg font-semibold mb-4">📃 Daftar Anggota</h2>
      <table class="w-full border text-sm">
        <thead class="bg-gray-100 text-left">
          <tr>
            <th class="border px-3 py-2">ID</th>
            <th class="border px-3 py-2">Nama</th>
            <th class="border px-3 py-2">Username</th>
            <th class="border px-3 py-2">NIK</th>
            <th class="border px-3 py-2">Alamat</th>
            <th class="border px-3 py-2">Tinggal</th>
            <th class="border px-3 py-2">Agama</th>
            <th class="border px-3 py-2">JK</th>
            <th class="border px-3 py-2">Status</th>
            <th class="border px-3 py-2">Penghasilan</th>
            <th class="border px-3 py-2">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $res = mysqli_query($conn, "SELECT u.*, d.* FROM users u 
                    LEFT JOIN anggota_detail d ON u.id = d.user_id WHERE u.role='anggota'");
            while ($r = mysqli_fetch_assoc($res)) {
              echo "<tr class='hover:bg-gray-50'>
              <form method='POST'>
                <input type='hidden' name='id' value='{$r['id']}'>
                <td class='border px-2 py-1'>{$r['id']}</td>
                <td class='border px-2 py-1'><input name='nama' value='{$r['nama']}' class='border p-1 w-full'></td>
                <td class='border px-2 py-1'><input name='username' value='{$r['username']}' class='border p-1 w-full'></td>
                <td class='border px-2 py-1'><input name='nik' value='{$r['nik']}' class='border p-1 w-full'></td>
                <td class='border px-2 py-1'><input name='alamat' value='{$r['alamat']}' class='border p-1 w-full'></td>
                <td class='border px-2 py-1'><input name='alamat_tinggal' value='{$r['alamat_tinggal']}' class='border p-1 w-full'></td>
                <td class='border px-2 py-1'><input name='agama' value='{$r['agama']}' class='border p-1 w-full'></td>
                <td class='border px-2 py-1'><input name='jenis_kelamin' value='{$r['jenis_kelamin']}' class='border p-1 w-full'></td>
                <td class='border px-2 py-1'><input name='status' value='{$r['status']}' class='border p-1 w-full'></td>
                <td class='border px-2 py-1'><input name='penghasilan' value='{$r['penghasilan']}' class='border p-1 w-full'></td>
                <td class='border px-2 py-1 text-center space-y-1'>
                  <button name='edit' class='bg-yellow-500 text-white px-2 py-1 rounded'>Edit</button>
                  <button name='reset' class='bg-blue-500 text-white px-2 py-1 rounded'>Reset</button><br>
                  <a href='?delete={$r['id']}' onclick=\"return confirm('Hapus anggota ini?')\" class='bg-red-600 text-white px-2 py-1 rounded inline-block'>Hapus</a>
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
