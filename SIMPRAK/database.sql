CREATE DATABASE IF NOT EXISTS `pengumpulantugas`;
USE `pengumpulantugas`;

-- Tabel untuk pengguna (mahasiswa dan asisten)
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('mahasiswa','asisten') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel untuk mata praktikum
CREATE TABLE `mata_praktikum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_praktikum` varchar(20) NOT NULL,
  `nama_praktikum` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `asisten_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_praktikum` (`kode_praktikum`),
  KEY `asisten_id` (`asisten_id`),
  CONSTRAINT `fk_asisten` FOREIGN KEY (`asisten_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel untuk pendaftaran praktikum oleh mahasiswa
CREATE TABLE `pendaftaran_praktikum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` int(11) NOT NULL,
  `praktikum_id` int(11) NOT NULL,
  `tanggal_daftar` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `mahasiswa_praktikum` (`mahasiswa_id`, `praktikum_id`),
  KEY `mahasiswa_id` (`mahasiswa_id`),
  KEY `praktikum_id` (`praktikum_id`),
  CONSTRAINT `fk_mahasiswa_pendaftaran` FOREIGN KEY (`mahasiswa_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_praktikum_pendaftaran` FOREIGN KEY (`praktikum_id`) REFERENCES `mata_praktikum` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel untuk modul dalam setiap praktikum
CREATE TABLE `modul` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `praktikum_id` int(11) NOT NULL,
  `judul_modul` varchar(255) NOT NULL,
  `deskripsi_modul` text DEFAULT NULL,
  `file_materi` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `praktikum_id` (`praktikum_id`),
  CONSTRAINT `fk_praktikum_modul` FOREIGN KEY (`praktikum_id`) REFERENCES `mata_praktikum` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel untuk laporan yang dikumpulkan mahasiswa
CREATE TABLE `laporan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modul_id` int(11) NOT NULL,
  `mahasiswa_id` int(11) NOT NULL,
  `file_laporan` varchar(255) NOT NULL,
  `tanggal_kumpul` timestamp NOT NULL DEFAULT current_timestamp(),
  `nilai` int(3) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `tanggal_nilai` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `modul_id` (`modul_id`),
  KEY `mahasiswa_id` (`mahasiswa_id`),
  CONSTRAINT `fk_modul_laporan` FOREIGN KEY (`modul_id`) REFERENCES `modul` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mahasiswa_laporan` FOREIGN KEY (`mahasiswa_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;