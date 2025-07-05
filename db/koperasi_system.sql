-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 05 Jul 2025 pada 07.19
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `koperasi_system`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `anggota_detail`
--

CREATE TABLE `anggota_detail` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `alamat_tinggal` text DEFAULT NULL,
  `agama` varchar(20) DEFAULT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `penghasilan` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `anggota_detail`
--

INSERT INTO `anggota_detail` (`id`, `user_id`, `nama`, `nik`, `alamat`, `alamat_tinggal`, `agama`, `jenis_kelamin`, `status`, `penghasilan`) VALUES
(1, 5, 'willy', '1233321', 'cikarang', 'tegal', 'segawon', 'Laki-laki', 'ngrowot', 1500000),
(2, 8, 'dickY', '6556784', 'ngawi ', 'mojomok', 'islam', 'Laki-laki', 'sudah kawin', 12000000),
(3, 9, 'ahmad', '35647893', 'kediri', 'kediri', 'islam', 'Laki-laki', 'belum kawin', 1500000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `angsuran_log`
--

CREATE TABLE `angsuran_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `pinjaman_id` int(11) DEFAULT NULL,
  `bayar` int(11) DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pinjaman`
--

CREATE TABLE `pinjaman` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tanggal` datetime DEFAULT NULL,
  `tenggat` date DEFAULT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `jaminan` varchar(255) DEFAULT NULL,
  `angsuran` int(11) DEFAULT NULL,
  `status` enum('belum lunas','lunas') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pinjaman`
--

INSERT INTO `pinjaman` (`id`, `user_id`, `tanggal`, `tenggat`, `jumlah`, `jaminan`, `angsuran`, `status`, `created_at`) VALUES
(1, 3, '2025-06-20 12:43:32', NULL, 99999999.99, 'bpkp motor ', 2000000, '', '2025-06-20 12:43:32'),
(2, 1, '2025-06-22 11:29:58', NULL, 1000000.00, 'bpkp motor ', 2000000, '', '2025-06-22 11:29:58'),
(3, 3, '2025-06-22 11:33:12', NULL, 2000000.00, 'laptop', 200000, '', '2025-06-22 11:33:12'),
(4, 2, '2025-06-24 12:40:21', NULL, 500000.00, 'bpkp motor ', 25000, '', '2025-06-24 12:40:21'),
(6, 4, '2025-06-24 15:30:10', NULL, 800000.00, 'akte kelahiran', 60000, '', '2025-06-24 15:30:10'),
(7, 1, '2025-06-24 15:44:06', NULL, 80000000.00, 'thpx', 7000000, '', '2025-06-24 15:44:06'),
(8, 1, '2025-06-24 15:52:09', NULL, 80000000.00, 'thpx', 7000000, '', '2025-06-24 15:52:09'),
(9, 3, '2025-06-24 16:13:44', NULL, 300000.00, 'akte kelahiran', 2000, '', '2025-06-24 16:13:44'),
(10, 3, '2025-06-24 16:15:56', NULL, 300000.00, 'akte kelahiran', 2000, '', '2025-06-24 16:15:56'),
(11, 3, '2025-06-24 16:20:02', NULL, 8000000.00, 'surat kematian', 700000, '', '2025-06-24 16:20:02'),
(12, 3, '2025-06-24 16:21:12', NULL, 8000000.00, 'surat kematian', 700000, '', '2025-06-24 16:21:12'),
(13, 1, '2025-06-24 21:41:06', NULL, 120000.00, 'bpkb', 12, '', '2025-06-24 21:41:06'),
(14, 3, '2025-06-24 21:58:22', NULL, 120000.00, 'bpkb', 12, '', '2025-06-24 21:58:22'),
(15, 1, '2025-06-24 22:28:34', NULL, 120000.00, 'bpkb', 12, '', '2025-06-24 22:28:34'),
(16, 1, '2025-06-24 22:29:51', NULL, 120000.00, 'bpkb', 12, '', '2025-06-24 22:29:51'),
(17, 2, '2025-06-25 11:18:37', NULL, 120000.00, 'bpkb', 12, '', '2025-06-25 11:18:37'),
(18, 2, '2025-06-25 11:21:42', NULL, 120000.00, 'bpkb', 12, '', '2025-06-25 11:21:42'),
(19, 2, '2025-06-25 11:26:05', NULL, 120000.00, 'bpkb', 12, '', '2025-06-25 11:26:05'),
(20, 1, '2025-06-29 14:00:04', NULL, 120000.00, 'bpkb', 12, '', '2025-06-29 14:00:04'),
(21, 1, '2025-06-29 14:06:51', NULL, 1200000.00, 'akte kelahiran', 12, '', '2025-06-29 14:06:51'),
(22, 2, '2025-07-03 15:19:59', NULL, 1200000.00, 'akte kelahiran', 12, '', '2025-07-03 15:19:59'),
(23, 2, '2025-07-03 15:20:13', NULL, 1200000.00, 'akte kelahiran', 12, '', '2025-07-03 15:20:13'),
(24, 4, '2025-07-03 15:28:09', NULL, 1200000.00, 'akte kelahiran', 12, '', '2025-07-03 15:28:09'),
(25, 4, '2025-07-03 15:28:30', NULL, 1200000.00, 'akte kelahiran', 12, '', '2025-07-03 15:28:30'),
(26, 3, '2025-07-04 00:14:06', NULL, 1600000.00, 'ktp', 200000, '', '2025-07-04 00:14:06'),
(29, 3, '2025-07-04 14:41:31', NULL, 100000.00, 'ktp', 5000, '', '2025-07-04 14:41:31'),
(30, 3, '2025-07-04 14:43:54', NULL, 500000.00, 'ktp', 20000, '', '2025-07-04 14:43:54');

-- --------------------------------------------------------

--
-- Struktur dari tabel `simpanan`
--

CREATE TABLE `simpanan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tanggal` datetime DEFAULT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `metode` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` enum('pending','disetujui','ditolak','lunas') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `simpanan`
--

INSERT INTO `simpanan` (`id`, `user_id`, `tanggal`, `jumlah`, `metode`, `created_at`, `status`) VALUES
(1, 3, '2025-06-20 00:00:00', 100000.00, 'Manual Input', '2025-06-20 11:38:06', 'disetujui'),
(2, 3, '2025-06-20 00:00:00', 500000.00, NULL, '2025-06-20 11:42:38', 'disetujui'),
(3, 3, '2025-07-04 00:12:50', 12333.00, 'Manual Input', '2025-07-04 00:12:50', 'pending'),
(4, 3, '2025-07-04 00:00:00', 2000000.00, NULL, '2025-07-04 14:15:41', 'disetujui'),
(5, 3, '2025-07-04 00:00:00', 45000000.00, NULL, '2025-07-04 14:16:48', 'disetujui'),
(6, 3, '2025-07-04 00:00:00', 1500000.00, NULL, '2025-07-04 14:21:24', 'disetujui'),
(7, 3, '2025-07-04 14:36:25', 100000.00, 'Manual Input', '2025-07-04 14:36:25', 'pending'),
(8, 3, '2025-07-04 14:43:24', 100000.00, 'Manual Input', '2025-07-04 14:43:24', 'pending');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('anggota','petugas','pimpinan') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'Petugas Satu', 'petugas1', '$2b$12$x/ICBuVVRPrmf9evFFJ/Uuttqe65vlssObWacp0m3nGpu7pOnqt86', 'petugas', '2025-06-19 16:49:33'),
(2, 'Pimpinan Satu', 'pimpinan1', '$2b$12$x/ICBuVVRPrmf9evFFJ/Uuttqe65vlssObWacp0m3nGpu7pOnqt86', 'pimpinan', '2025-06-19 16:49:33'),
(3, 'Anggota Satu', 'anggota1', '$2b$12$x/ICBuVVRPrmf9evFFJ/Uuttqe65vlssObWacp0m3nGpu7pOnqt86', 'anggota', '2025-06-19 16:49:33'),
(4, 'nidea', 'nidea', '$2y$10$OvkGKWZaShKw1HwX18XQy.bD.z5K9pPDB9UgnlN4PQ5Bnnz4yFM7m', 'anggota', '2025-06-20 03:01:06'),
(5, 'willy', 'anggota2', '$2y$10$uB6TuJomACiUKElVO5j9geFmFE6QeDTIGLNMsf67vI9M.LFYykP/i', 'anggota', '2025-07-03 10:15:02'),
(8, 'dickY', 'dickY', '$2y$10$vekpsreYAv81QVnwi/wMteLEQhfQCnZ0jbRcrmxIy8qYVn1eqhFyS', 'anggota', '2025-07-04 07:08:14'),
(9, 'ahmad', 'ahmad', '$2y$10$DhhnHMomnZB0YMv8jOzHvu48dWPDN7vsqkzbn5hMjimxYIwQLVXna', 'anggota', '2025-07-04 07:19:50');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `anggota_detail`
--
ALTER TABLE `anggota_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `angsuran_log`
--
ALTER TABLE `angsuran_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pinjaman`
--
ALTER TABLE `pinjaman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `simpanan`
--
ALTER TABLE `simpanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `anggota_detail`
--
ALTER TABLE `anggota_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `angsuran_log`
--
ALTER TABLE `angsuran_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pinjaman`
--
ALTER TABLE `pinjaman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT untuk tabel `simpanan`
--
ALTER TABLE `simpanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `anggota_detail`
--
ALTER TABLE `anggota_detail`
  ADD CONSTRAINT `anggota_detail_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pinjaman`
--
ALTER TABLE `pinjaman`
  ADD CONSTRAINT `pinjaman_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `simpanan`
--
ALTER TABLE `simpanan`
  ADD CONSTRAINT `simpanan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
