-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 25 Okt 2024 pada 08.57
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
-- Database: `event_registration`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `max_participants` int(11) NOT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `status` enum('open','closed','canceled') NOT NULL DEFAULT 'open',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_hot` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `events`
--

INSERT INTO `events` (`id`, `name`, `event_date`, `event_time`, `location`, `description`, `max_participants`, `banner_image`, `status`, `created_by`, `created_at`, `is_hot`) VALUES
(5, 'Lolly Cafe', '2024-10-23', '11:07:00', 'BANGKA BELITUNG', 'Seventeen adalah sebuah boy band asal Korea Selatan yang dibentuk oleh Pledis Entertainment. Grup yang terdiri dari 13 anggota ini dibagi berdasarkan spesialisasi keahlian masing-masing ke dalam 3 sub-unit; hip-hop unit, vocal unit, dan performance unit. Seventeen adalah sebuah boy band asal Korea Selatan yang dibentuk oleh Pledis Entertainment. Grup yang terdiri dari 13 anggota ini dibagi berdasarkan spesialisasi keahlian masing-masing ke dalam 3 sub-unit; hip-hop unit, vocal unit, dan performance unit. \r\n', 500, '1729764793_ea5a13f1b466854fb507c0746b477745.webp', 'canceled', 3, '2024-10-22 15:30:47', 0),
(6, 'Seventeen', '2025-02-12', '11:00:00', 'KOREAN', 'LIMITED EDITION GUYSS!!\r\nSeventeen adalah sebuah boy band asal Korea Selatan yang dibentuk oleh Pledis Entertainment. Grup yang terdiri dari 13 anggota ini dibagi berdasarkan spesialisasi keahlian masing-masing ke dalam 3 sub-unit; hip-hop unit, vocal unit, dan performance unit. Seventeen adalah sebuah boy band asal Korea Selatan yang dibentuk oleh Pledis Entertainment. Grup yang terdiri dari 13 anggota ini dibagi berdasarkan spesialisasi keahlian masing-masing ke dalam 3 sub-unit; hip-hop unit, vocal unit, dan performance unit. ', 20, '1729764655_seventeen-boyband-k-pop_169.jpeg', 'open', 3, '2024-10-22 16:34:10', 1),
(7, 'ASTRO', '2025-02-02', '03:03:00', 'PALEMBANG', 'Astro adalah boy band Korea Selatan yang dibentuk oleh Fantagio. Grup ini terdiri dari 4 anggota: MJ, JinJin, Eunwoo, dan Yoon Sanha. Awalnya beranggotakan 6 orang, Rocky meninggalkan grup pada 28 Februari 2023. Pada 19 April 2023, Moon Bin dinyatakan meninggal di rumahnya.', 1000, '1729764742_astro_169.jpeg', 'open', 3, '2024-10-24 05:20:34', 0),
(8, 'Billie Eilish', '2026-02-02', '01:00:00', 'NGANJUK', 'Billie Eilish Pirate Baird O\'Connell adalah seorang penyanyi dan penulis lagu asal Amerika Serikat. Dia pertama kali mendapat perhatian publik pada tahun 2015 dengan singel debutnya \"Ocean Eyes\"', 10, '1729764907_9CD92C02-0612-4B29-9544-96FFBBDD35D9.jpeg', 'open', 3, '2024-10-24 10:15:07', 0),
(10, 'aespa', '2024-10-25', '02:57:00', 'Kwangya', 'Aespa is a South Korean girl group formed by SM Entertainment. The group consists of four members: Karina, Giselle, Winter, and Ningning. The group is known for popularizing the metaverse concept and hyperpop music in K-pop.', 1000, '1729799890_aespa_whiplash.jpg', 'open', 8, '2024-10-24 19:58:10', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `hot_events`
--

CREATE TABLE `hot_events` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `hot_events`
--

INSERT INTO `hot_events` (`id`, `event_id`) VALUES
(60, 6),
(59, 10);

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expiry` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `registrations`
--

CREATE TABLE `registrations` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('registered','canceled') NOT NULL DEFAULT 'registered'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `registrations`
--

INSERT INTO `registrations` (`id`, `event_id`, `user_id`, `registration_date`, `status`) VALUES
(2, 5, 4, '2024-10-22 15:40:03', 'registered'),
(5, 6, 4, '2024-10-22 17:05:39', 'registered'),
(6, 6, 6, '2024-10-24 03:38:16', 'canceled'),
(10, 8, 6, '2024-10-24 10:16:43', 'registered'),
(16, 7, 7, '2024-10-25 00:09:39', 'registered');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `profile_image`, `role`, `created_at`) VALUES
(3, 'admin', 'admin@yahoo.com', '$2y$10$/zkLUedcEkDLjb1A3kedGeVLfiaUFD1DhbW9Aws6CBntY8NMN5U/G', NULL, 'admin', '2024-10-22 13:28:07'),
(4, 'fedora', 'apa@gmail.com', '$2y$10$XejXxGScWkECMnFxbr/MeeKjgk1wVu4m1wQfDApeRY9NoCsKCat5W', NULL, 'user', '2024-10-22 13:43:51'),
(6, 'lala', 'lala@gmail.com', '$2y$10$Yucsrc5ACDSqLLy4c3VzeuNqO2SqbsW6OHrFyHUbBxBGCEzpLUSDO', 'profile_6719deab128d5.jpg', 'user', '2024-10-24 03:37:14'),
(7, 'Karina', 'Karina@kwangya.com', '$2y$10$SMDAiIFTX8tL6tJBPd3uGeRwP2i1bUR9AgJbkvTWHViW.dDy.1x.W', 'profile_671ad5b9cfa77.jpg', 'user', '2024-10-24 05:50:25'),
(8, 'admin2', 'admin@gmail.com', '$2y$10$b.vNoO1X.vGLJxQZs5sEyO54.sHs9UoCIPlZB7DOxtd3obNy/e0Cm', NULL, 'admin', '2024-10-24 10:32:51'),
(9, 'fahmy', 'fahmyarwaltz@gmail.com', '$2y$10$OyRJGFRw11f4rAmJVyJFe.bZHH2xUmiSS9s3GhV3JM.5exJxn/y8y', NULL, 'user', '2024-10-25 06:30:17');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indeks untuk tabel `hot_events`
--
ALTER TABLE `hot_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indeks untuk tabel `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `token` (`token`),
  ADD KEY `expiry` (`expiry`);

--
-- Indeks untuk tabel `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `hot_events`
--
ALTER TABLE `hot_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT untuk tabel `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `registrations`
--
ALTER TABLE `registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `hot_events`
--
ALTER TABLE `hot_events`
  ADD CONSTRAINT `hot_events_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`),
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
