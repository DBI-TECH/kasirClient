-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 16, 2026 at 11:16 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kasir_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_admin` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `password`, `nama_admin`, `created_at`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'Administrator', '2026-06-16 11:09:05');

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id_barang` int NOT NULL,
  `nama_barang` varchar(100) DEFAULT NULL,
  `harga` int DEFAULT NULL,
  `stok` int DEFAULT '0',
  `tipe` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id_barang`, `nama_barang`, `harga`, `stok`, `tipe`) VALUES
(1, 'Cranora', 18000, 50, 'mocktail'),
(2, 'Solvia', 18000, 50, 'mocktail'),
(3, 'Brezza', 18000, 50, 'mocktail'),
(4, 'Matcha', 18000, 48, 'milk base'),
(5, 'Chocolate', 18000, 50, 'milk base'),
(6, 'Red Velvet', 18000, 50, 'milk base'),
(7, 'Milky Berry', 18000, 50, 'milk base'),
(8, 'Tubruk', 12000, 50, 'coffe'),
(9, 'Berryboo', 18000, 50, 'coffe'),
(10, 'Moora', 16000, 50, 'coffe'),
(11, 'Americano', 15000, 50, 'coffe'),
(12, 'Caffra', 16000, 48, 'coffe'),
(13, 'Cheese Roll', 15000, 50, 'snack'),
(14, 'BBQ French Fries', 15000, 50, 'snack'),
(15, 'Mix Platter', 18000, 50, 'snack'),
(16, 'late', 15, 50, 'coffe');

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail` int NOT NULL,
  `id_transaksi` int DEFAULT NULL,
  `id_barang` int DEFAULT NULL,
  `jumlah` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail`, `id_transaksi`, `id_barang`, `jumlah`) VALUES
(1, 1, 8, 1),
(2, 1, 9, 1),
(3, 1, 10, 1),
(4, 1, 12, 1),
(5, 1, 4, 1),
(6, 1, 5, 1),
(7, 1, 6, 1),
(8, 1, 7, 1),
(9, 1, 1, 1),
(10, 1, 2, 1),
(11, 1, 3, 1),
(12, 1, 13, 1),
(13, 1, 14, 1),
(14, 1, 15, 1),
(15, 1, 11, 1),
(16, 2, 8, 1),
(17, 2, 9, 1),
(18, 2, 5, 1),
(19, 2, 6, 1),
(20, 2, 1, 1),
(21, 2, 2, 1),
(22, 2, 13, 1),
(23, 2, 14, 1),
(24, 2, 15, 1),
(25, 2, 10, 1),
(26, 2, 11, 1),
(27, 2, 12, 1),
(28, 2, 4, 1),
(29, 2, 7, 1),
(30, 2, 3, 1),
(31, 3, 8, 1),
(32, 4, 8, 1),
(67, 8, 8, 1),
(68, 8, 11, 1),
(69, 9, 8, 6),
(70, 10, 8, 1),
(71, 10, 9, 1),
(72, 10, 10, 1),
(73, 10, 11, 1),
(74, 10, 12, 3),
(75, 10, 7, 3),
(76, 10, 3, 3),
(77, 10, 15, 3),
(79, 12, 8, 1),
(80, 12, 11, 1),
(81, 12, 14, 1),
(82, 12, 15, 5),
(83, 13, 15, 20),
(85, 15, 11, 1),
(86, 15, 14, 1),
(87, 16, 10, 2),
(88, 17, 13, 3),
(89, 18, 4, 2),
(90, 18, 7, 1),
(91, 19, 4, 2),
(92, 20, 4, 2),
(93, 21, 2, 2),
(94, 22, 12, 2),
(95, 23, 4, 3),
(96, 24, 9, 5),
(97, 25, 13, 1),
(98, 26, 13, 1),
(99, 26, 14, 1),
(100, 27, 8, 2),
(101, 27, 11, 1),
(102, 28, 8, 3),
(103, 29, 9, 2),
(104, 30, 6, 1),
(105, 31, 11, 3),
(106, 32, 6, 2),
(107, 33, 10, 1),
(108, 34, 6, 1),
(109, 35, 9, 2),
(110, 36, 13, 2),
(111, 37, 13, 1),
(112, 38, 13, 1),
(113, 39, 13, 1),
(114, 40, 11, 1),
(115, 41, 8, 2),
(116, 42, 15, 3),
(117, 43, 14, 1),
(118, 44, 8, 3),
(119, 45, 13, 2),
(120, 46, 5, 2),
(121, 47, 6, 1),
(122, 48, 5, 2),
(123, 49, 14, 1),
(124, 50, 9, 1),
(125, 51, 8, 1),
(126, 52, 8, 8),
(127, 53, 8, 3),
(128, 53, 14, 2),
(129, 54, 4, 1),
(130, 55, 12, 1);

--
-- Triggers `detail_transaksi`
--
DELIMITER $$
CREATE TRIGGER `cek_stok_sebelum_transaksi` BEFORE INSERT ON `detail_transaksi` FOR EACH ROW BEGIN
    DECLARE stok_tersedia INT;
    SELECT stok INTO stok_tersedia FROM barang WHERE id_barang = NEW.id_barang;
    IF stok_tersedia < NEW.jumlah THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Stok tidak mencukupi!';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `kurangi_stok_setelah_transaksi` AFTER INSERT ON `detail_transaksi` FOR EACH ROW BEGIN
    UPDATE barang 
    SET stok = stok - NEW.jumlah 
    WHERE id_barang = NEW.id_barang;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `kasir`
--

CREATE TABLE `kasir` (
  `id_kasir` int NOT NULL,
  `nama_kasir` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kasir`
--

INSERT INTO `kasir` (`id_kasir`, `nama_kasir`) VALUES
(1, 'ucok'),
(2, 'Budi'),
(3, 'Andi');

-- --------------------------------------------------------

--
-- Table structure for table `stok`
--

CREATE TABLE `stok` (
  `id_stok` int NOT NULL,
  `nama_stok` varchar(100) DEFAULT NULL,
  `stok` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `stok`
--

INSERT INTO `stok` (`id_stok`, `nama_stok`, `stok`) VALUES
(2, 'gula', 1),
(3, 'gula', 1),
(4, 'garam', 1),
(5, 'cabai ', 2),
(6, 'garam', 2),
(7, 'garam', 2),
(8, 'es batu', 20),
(9, 'es batu', 20),
(10, 'garam', 11);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int NOT NULL,
  `tgl_transaksi` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `total` int DEFAULT NULL,
  `nama_pemesan` varchar(150) DEFAULT NULL,
  `cash` int NOT NULL DEFAULT '0',
  `change` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `tgl_transaksi`, `total`, `nama_pemesan`, `cash`, `change`) VALUES
(1, '2026-05-28 10:52:32', 251000, 'iqbal', 0, 0),
(2, '2026-05-28 11:03:38', 251000, 'bibur', 0, 0),
(3, '2026-05-28 11:33:11', 12000, 'iqbal', 0, 0),
(4, '2026-05-28 18:03:43', 12000, 'daffa', 0, 0),
(8, '2026-05-29 00:19:50', 27000, 'daffa', 0, 0),
(9, '2026-05-29 00:51:04', 72000, 'daffa', 0, 0),
(10, '2026-05-29 19:48:56', 271000, 'ilyas', 0, 0),
(12, '2026-05-29 20:39:25', 132000, 'ilyas', 0, 0),
(13, '2026-05-29 20:45:41', 360000, 'fg', 0, 0),
(15, '2026-05-29 22:35:31', 30000, 'daffa', 0, 0),
(16, '2026-05-29 22:50:17', 32000, 'daffa', 0, 0),
(17, '2026-05-30 00:09:02', 45000, 'daffa5', 0, 0),
(18, '2026-05-30 14:09:16', 54000, 'audi', 0, 0),
(19, '2026-05-30 14:40:13', 36000, 'audi', 0, 0),
(20, '2026-05-30 14:45:42', 36000, 'audi', 0, 0),
(21, '2026-05-30 14:57:01', 36000, 'daffa5', 0, 0),
(22, '2026-05-30 22:43:14', 32000, 'daffa', 0, 0),
(23, '2026-05-30 22:50:57', 54000, 'audi', 0, 0),
(24, '2026-05-30 22:56:05', 90000, 'bibur', 0, 0),
(25, '2026-05-31 02:35:07', 15000, 'aqil', 0, 0),
(26, '2026-05-31 03:00:26', 30000, 'agol', 0, 0),
(27, '2026-06-10 12:43:42', 39000, 'aan', 0, 0),
(28, '2026-06-10 12:45:02', 36000, 'nna', 0, 0),
(29, '2026-06-10 14:04:41', 36000, 'uud', 0, 0),
(30, '2026-06-10 14:05:33', 18000, 'uud', 0, 0),
(31, '2026-06-10 14:05:48', 45000, 'audi', 0, 0),
(32, '2026-06-10 14:06:33', 36000, 'audi', 0, 0),
(33, '2026-06-10 14:07:22', 16000, 'uud', 0, 0),
(34, '2026-06-10 14:08:42', 18000, 'iqbal', 0, 0),
(35, '2026-06-10 14:10:35', 36000, 'waa', 0, 0),
(36, '2026-06-10 14:29:01', 30000, 'iqbal', 0, 0),
(37, '2026-06-10 14:36:37', 15000, 'audi', 0, 0),
(38, '2026-06-10 15:18:46', 15000, 'iqbal', 50000, 35000),
(39, '2026-06-10 15:19:14', 15000, 'audi', 50000, 35000),
(40, '2026-06-10 15:21:59', 15000, 'bibur', 15000, 0),
(41, '2026-06-10 15:22:40', 24000, 'nnaaa', 24000, 0),
(42, '2026-06-10 15:25:04', 54000, 'iqbal', 60000, 6000),
(43, '2026-06-10 15:25:54', 15000, 'iqbal', 20000, 5000),
(44, '2026-06-10 15:50:13', 36000, 'bibur', 50000, 14000),
(45, '2026-06-10 15:52:11', 30000, 'iqbal', 50000, 20000),
(46, '2026-06-11 11:21:25', 36000, 'aaaaaa', 50000, 14000),
(47, '2026-06-11 11:33:26', 18000, 'bbb', 50000, 32000),
(48, '2026-06-11 11:39:28', 36000, 'eeee', 100000, 64000),
(49, '2026-06-11 11:41:44', 15000, 'iqbal', 20000, 5000),
(50, '2026-06-11 11:51:30', 18000, 'gg', 20000, 2000),
(51, '2026-06-11 12:10:40', 12000, 'ii', 20000, 8000),
(52, '2026-06-11 12:30:05', 96000, 'hhh', 100000, 4000),
(53, '2026-06-11 12:30:25', 66000, 'Asw', 100000, 34000),
(54, '2026-06-16 11:13:11', 18000, 'audi', 40000, 22000),
(55, '2026-06-16 11:14:03', 16000, 'xsc', 20000, 4000);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`);

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_barang` (`id_barang`);

--
-- Indexes for table `kasir`
--
ALTER TABLE `kasir`
  ADD PRIMARY KEY (`id_kasir`);

--
-- Indexes for table `stok`
--
ALTER TABLE `stok`
  ADD PRIMARY KEY (`id_stok`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `id_barang` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id_detail` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT for table `kasir`
--
ALTER TABLE `kasir`
  MODIFY `id_kasir` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stok`
--
ALTER TABLE `stok`
  MODIFY `id_stok` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`),
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
