-- =============================================================================
-- karyaNovel - Skema Database MySQL Resmi
-- Sesuai Spesifikasi PRD, SDD, & Kebutuhan MVC karyaNovel
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `karyanovel_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `karyanovel_db`;

-- Matikan foreign key check sementara saat migrasi
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Tabel users (Multi-Aktor & Verifikasi OTP WhatsApp)
DROP TABLE IF EXISTS `ai_histories`;
DROP TABLE IF EXISTS `chapters`;
DROP TABLE IF EXISTS `novels`;
DROP TABLE IF EXISTS `notification_logs`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `phone` VARCHAR(25) NULL,
    `password` VARCHAR(255) NOT NULL,
    `otp_code` VARCHAR(10) NULL,
    `otp_expiry` DATETIME NULL,
    `is_verified` TINYINT(1) DEFAULT 1,
    `role` ENUM('pembaca', 'penulis', 'admin', 'user') DEFAULT 'pembaca',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_email` (`email`),
    INDEX `idx_users_phone` (`phone`),
    INDEX `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel novels (Koleksi Naskah Cerita)
CREATE TABLE `novels` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `synopsis` TEXT NOT NULL,
    `genre` VARCHAR(100) NOT NULL,
    `cover` VARCHAR(255) NULL,
    `status` ENUM('draft', 'published') DEFAULT 'draft',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_novels_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    INDEX `idx_novels_user_id` (`user_id`),
    INDEX `idx_novels_status` (`status`),
    INDEX `idx_novels_genre` (`genre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel chapters (Daftar Bab & Konten Naskah)
CREATE TABLE `chapters` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `novel_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `content` LONGTEXT NOT NULL,
    `chapter_order` INT NOT NULL DEFAULT 1,
    `status` ENUM('draft', 'published') DEFAULT 'draft',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_chapters_novel` FOREIGN KEY (`novel_id`) REFERENCES `novels` (`id`) ON DELETE CASCADE,
    INDEX `idx_chapters_novel_order` (`novel_id`, `chapter_order`),
    INDEX `idx_chapters_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabel ai_histories (Riwayat Prompt & Respons AI Studio)
CREATE TABLE `ai_histories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `chapter_id` INT NULL,
    `prompt` TEXT NOT NULL,
    `response` LONGTEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_ai_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ai_chapter` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE SET NULL,
    INDEX `idx_ai_user_id` (`user_id`),
    INDEX `idx_ai_chapter_id` (`chapter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabel settings (Konfigurasi Dinamis AI Chatbot & Parameter Sistem)
CREATE TABLE `settings` (
    `key_name` VARCHAR(100) NOT NULL PRIMARY KEY,
    `key_value` LONGTEXT NULL,
    `description` VARCHAR(255) NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabel notification_logs (Riwayat Forward Notifikasi WhatsApp via Fonnte Gateway)
CREATE TABLE `notification_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `type` VARCHAR(50) NOT NULL,
    `target` VARCHAR(255) NOT NULL,
    `title` VARCHAR(255) NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('success', 'failed') NOT NULL DEFAULT 'success',
    `response` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_notif_type` (`type`),
    INDEX `idx_notif_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- DATA SEEDER AWAL (Default Akun Uji Coba Multi-Aktor & Novel Contoh)
-- Password default seluruh akun uji coba: 'password123'
-- Bcrypt Hash: $2y$10$lZP6yspwf1XcIv5w9PnE3uI3TF.AnT1rdo.FI9rlwHOq2A9F5cuxa
-- =============================================================================

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `is_verified`, `role`) VALUES
(1, 'Administrator karyaNovel', 'admin@karyanovel.id', '089634700208', '$2y$10$lZP6yspwf1XcIv5w9PnE3uI3TF.AnT1rdo.FI9rlwHOq2A9F5cuxa', 1, 'admin'),
(2, 'Ahmad Rian', 'penulis1@karyanovel.id', '081234567891', '$2y$10$lZP6yspwf1XcIv5w9PnE3uI3TF.AnT1rdo.FI9rlwHOq2A9F5cuxa', 1, 'penulis'),
(3, 'Clara Sinta', 'penulis2@karyanovel.id', '081234567892', '$2y$10$lZP6yspwf1XcIv5w9PnE3uI3TF.AnT1rdo.FI9rlwHOq2A9F5cuxa', 1, 'penulis'),
(4, 'Budi Pembaca', 'pembaca.test@karyanovel.test', '081234567893', '$2y$10$lZP6yspwf1XcIv5w9PnE3uI3TF.AnT1rdo.FI9rlwHOq2A9F5cuxa', 1, 'pembaca');

INSERT INTO `novels` (`id`, `user_id`, `title`, `synopsis`, `genre`, `cover`, `status`) VALUES
(1, 2, 'Lembah Bintang: Menembus Horizon', 'Di dunia masa depan di mana ingatan manusia dapat diarsipkan ke dalam kristal kuantum, seorang penjelajah menemukan kristal terlarang yang menyimpan rahasia penciptaan galaksi.', 'Sci-Fi', 'public/images/novel-cover-1.svg', 'published'),
(2, 3, 'Detak Jantung di Ujung Senja', 'Pertemuan tak sengaja antara seorang musisi jalanan di Kyoto dan seorang arsitek muda yang kehilangan gairah hidupnya melahirkan simfoni cinta yang tak terduga.', 'Romansa', 'public/images/novel-cover-2.svg', 'published'),
(3, 2, 'Legenda Pedang Petir Nusantara', 'Kisah epik persilatan nusantara abad pertengahan. Seorang pendekar muda bertualang menguak misteri hilangnya perguruan pedang petir dan ramalan malam abadi.', 'Aksi & Silat', 'public/images/novel-cover-3.svg', 'published'),
(4, 3, 'Misteri Paviliun Nomor 13', 'Seorang detektif swasta disewa menyelidiki kematian misterius pewaris tunggal konglomerat di dalam kamar paviliun yang terkunci rapat dari dalam.', 'Misteri', 'public/images/novel-cover-4.svg', 'draft');

INSERT INTO `chapters` (`id`, `novel_id`, `title`, `content`, `chapter_order`, `status`) VALUES
(1, 1, 'Prolog: Fragmen yang Hilang', 'Hujan meteor redup melintasi cakrawala stasiun luar angkasa Zenith-9. Arka menatap layar monitor kuantumnya yang berkedip dengan kode anomali berfrekuensi 440Hz. Sebuah sinyal kuno dari koordinat yang telah dihapus 200 tahun lalu...', 1, 'published'),
(2, 1, 'Bab 1: Menembus Gerbang Langit', 'Kabut tebal perlahan membelah saat Bayu melangkah mendekati bibir tebing. Kilatan petir merah menyambar di kejauhan, memantulkan bayangan pedang pusaka yang terhunus di tangan kanannya. Ia tahu pertarungan ini bukan lagi tentang membalaskan dendam perguruan, melainkan mencegah gerbang dimensi langit runtuh sebelum fajar menyingsing.', 2, 'published'),
(3, 2, 'Bab 1: Melodi Stasiun Gion', 'Dentang lonceng stasiun berpadu dengan petikan senar gitar akustik yang basah oleh sisa hujan sore. Clara berhenti sejenak, membiarkan alunan nada minor itu meresap ke dalam hatinya yang telah lama membeku...', 1, 'published'),
(4, 3, 'Bab 1: Denting Pertama di Puncak Merapi', 'Suara benturan logam menggetarkan dedaunan pohon pinus di lereng bukit. Pendekar bertopeng hitam melompat mundur sepuluh langkah sambil menatap pedang petirnya yang berpendar keemasan...', 1, 'published');

INSERT INTO `ai_histories` (`id`, `user_id`, `chapter_id`, `prompt`, `response`) VALUES
(1, 2, 2, 'Lanjutkan adegan pertarungan saat kilatan petir menyambar dan Bayu melihat musuhnya.', 'Kilatan petir merah menyambar di kejauhan, memantulkan bayangan pedang pusaka yang terhunus di tangan kanannya. Dari balik kabut tebal, siluet naga giok perlahan muncul, mengaumkan getaran yang membuat tanah di bawah kaki mereka bergoncang hebat.');
