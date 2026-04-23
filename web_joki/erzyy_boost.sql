-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 05, 2026 at 10:05 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `erzyy_boost`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `tipe_layanan` varchar(50) DEFAULT 'Joki Piloting',
  `kode_order` varchar(20) NOT NULL,
  `nickname` varchar(100) NOT NULL,
  `id_server` varchar(50) NOT NULL,
  `login_via` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_akun` varchar(255) NOT NULL,
  `rank_awal` varchar(50) NOT NULL,
  `bintang_awal` int(11) NOT NULL,
  `rank_tujuan` varchar(50) NOT NULL,
  `bintang_tujuan` int(11) NOT NULL,
  `catatan` text DEFAULT NULL,
  `payment` varchar(50) NOT NULL,
  `harga` int(11) NOT NULL,
  `status` enum('Pending','Diproses','Selesai') DEFAULT 'Pending',
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `tipe_layanan`, `kode_order`, `nickname`, `id_server`, `login_via`, `email`, `password_akun`, `rank_awal`, `bintang_awal`, `rank_tujuan`, `bintang_tujuan`, `catatan`, `payment`, `harga`, `status`, `tanggal`) VALUES
(2, 'Joki Piloting', 'ERZ-26B88', 'mawkiti', '131212(2323)', 'Moonton', 'aempremcuy@gmail.com', 'chuaks', '6', 20, '8', 100, 'angela woiiii angela', 'QRIS', 2250000, 'Selesai', '2026-04-04 20:07:22'),
(3, 'Joki Piloting', 'ERZ-3FF99', 'syaz', '2223444', 'Moonton', 'syaz.com', 'arsel', '6', 24, '8', 24000, 'ling er', 'QRIS', 599650000, 'Selesai', '2026-04-05 05:50:57'),
(5, 'Joki Piloting', 'ERZ-27627', 'gulingtelor', '123444(2445)', 'Moonton', 'erere.com', '2323', '5', 13, '5', 15, 'req xp suyo kalit yuzonk', 'DANA', 18000, 'Selesai', '2026-04-05 07:05:40'),
(6, 'Mabar VIP', '', 'mawkiti', '2223334(4545)', '-', 'Privasi (Mabar VIP)', 'Privasi (Mabar VIP)', '7', 0, '8', 100, 'kalo ls aku rate jelekkk!!!!!', 'BCA', 2062500, 'Pending', '2026-04-05 07:55:35'),
(7, 'Mabar VIP', 'ERZ-E4BEE', 'mawkiti', '131442(2399)', '', '', '', '4', 3, '5', 15, 'mabaaarrrrr', 'QRIS', 229500, 'Selesai', '2026-04-05 08:00:23');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `nickname` varchar(100) NOT NULL,
  `bintang` int(11) NOT NULL,
  `pesan` text NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`id`, `nickname`, `bintang`, `pesan`, `tanggal`) VALUES
(1, 'mawkiti', 5, 'GG jir joki disini, erzy ganteng erzy wangy, luv erzy -mawkiti', '2026-04-04 20:14:07'),
(2, 'kino', 5, 'gelooo gacor', '2026-04-05 07:02:31'),
(3, 'syaz', 1, 'lama banget jir dikerjainnya', '2026-04-05 07:07:44'),
(4, 'gulingtelor', 5, 'ok', '2026-04-05 07:50:43'),
(5, 'mawkiti', 5, 'ywyyyyyyyy', '2026-04-05 08:01:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'rasya', 'cb6f85857e374b6832a9cfd1d15751d8');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
