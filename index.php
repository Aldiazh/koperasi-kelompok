<?php
session_start();
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$user'");
    $data = mysqli_fetch_assoc($query);

    if ($data && password_verify($pass, $data['password'])) {
        $_SESSION['id'] = $data['id'];
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['role'] = $data['role'];
        header("Location: dashboard/{$data['role']}.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login Koperasi</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-100 via-white to-green-100 h-screen flex items-center justify-center">
  <div class="bg-white shadow-2xl rounded-2xl p-8 w-full max-w-md animate-fade-in">
    <div class="text-center mb-6">
      <div class="text-5xl mb-2">🏦</div>
      <h2 class="text-2xl font-bold text-blue-700">Login Koperasi</h2>
      <p class="text-gray-500 text-sm">Masukkan data login Anda</p>
    </div>
    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
        <input type="text" name="username" placeholder="Masukkan username" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200" required>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input type="password" name="password" placeholder="••••••••" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200" required>
      </div>
      <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-2 rounded-lg hover:bg-blue-700 transition duration-200">Login</button>
    </form>
    <?php if (!empty($error)): ?>
      <p class="text-red-600 mt-4 text-center"><?= $error ?></p>
    <?php endif; ?>
  </div>

  <!-- Animasi sederhana -->
  <style>
    .animate-fade-in {
      animation: fade-in 0.6s ease-in-out both;
    }
    @keyframes fade-in {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</body>
</html>
