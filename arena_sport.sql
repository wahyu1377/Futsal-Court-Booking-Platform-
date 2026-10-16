-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 16, 2026 at 03:44 AM
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
-- Database: `arena_sport`
--

-- --------------------------------------------------------

--
-- Table structure for table `lapangan`
--

CREATE TABLE `lapangan` (
  `id` int(10) UNSIGNED NOT NULL,
  `owner_id` int(10) UNSIGNED DEFAULT NULL,
  `nama` varchar(120) NOT NULL,
  `jenis` varchar(30) NOT NULL,
  `kecamatan` varchar(80) NOT NULL,
  `kota` varchar(80) NOT NULL,
  `harga_per_jam` int(10) UNSIGNED NOT NULL,
  `rating` decimal(2,1) NOT NULL DEFAULT 0.0,
  `jumlah_ulasan` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `foto` varchar(255) DEFAULT NULL,
  `fasilitas` varchar(255) DEFAULT NULL,
  `keterangan` varchar(120) DEFAULT NULL,
  `tersedia_hari_ini` tinyint(1) NOT NULL DEFAULT 1,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lapangan`
--

INSERT INTO `lapangan` (`id`, `owner_id`, `nama`, `jenis`, `kecamatan`, `kota`, `harga_per_jam`, `rating`, `jumlah_ulasan`, `foto`, `fasilitas`, `keterangan`, `tersedia_hari_ini`, `aktif`, `created_at`) VALUES
(1, NULL, 'Champion Futsal Arena Cilandak', 'futsal', 'Cilandak Barat', 'Jakarta Selatan', 150000, 4.8, 124, 'champion-futsal.jpg', 'Rumput Sintetis,Indoor AC', 'Peralatan Lengkap', 1, 1, '2026-10-16 01:09:45'),
(2, NULL, 'Smash Futsal Kuningan', 'futsal', 'Setiabudi Kuningan', 'Jakarta Selatan', 75000, 4.9, 88, 'smash-Futsal.jpg', 'Karpet Vinyl BWF,Parkir Luas', 'Standar BWF Pro', 1, 1, '2026-10-16 01:09:45'),
(3, NULL, 'Garuda Futsal Pondok Indah', 'futsal', 'Pondok Indah', 'Jakarta Selatan', 130000, 4.6, 57, 'garuda-futsal.jpg', 'Vinyl,Outdoor', 'Kantin & Musala', 0, 1, '2026-10-16 01:09:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','owner') NOT NULL DEFAULT 'user',
  `failed_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password_hash`, `role`, `failed_attempts`, `locked_until`, `created_at`) VALUES
(1, 'Aditya', 'tes@gmail.com', NULL, '$2y$10$NquhIh3MGuFRkziYNIRuQOs005tCQcm8yl9Ok5lp01awvh1J3M5J2', 'user', 0, '2026-10-16 08:34:38', '2026-10-16 00:58:23'),
(2, 'Aji', NULL, '083875211788', '$2y$10$0Y9f9X9sIzjxsN5VZmWuD.p.5VgAgXhsXYE5wX7DFL4HbvaKe8qiK', 'user', 0, NULL, '2026-10-16 01:20:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `lapangan`
--
ALTER TABLE `lapangan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lapangan`
--
ALTER TABLE `lapangan`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `lapangan`
--
ALTER TABLE `lapangan`
  ADD CONSTRAINT `lapangan_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
