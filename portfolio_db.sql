-- Buat database jika belum ada
CREATE DATABASE IF NOT EXISTS portfolio_db;
USE portfolio_db;

-- Tabel untuk konten (artikel, podcast, video)
CREATE TABLE IF NOT EXISTS contents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    type ENUM('article', 'podcast', 'video') NOT NULL,
    excerpt TEXT NOT NULL,
    url VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel untuk subscribers newsletter
CREATE TABLE IF NOT EXISTS subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Hapus data lama jika ada (untuk testing)
TRUNCATE TABLE contents;

-- Insert dummy data
INSERT INTO contents (title, type, excerpt, url) VALUES
('Membangun Aplikasi Islami dengan PHP dan MySQL', 'article', 'Panduan langkah demi langkah membuat aplikasi jadwal sholat berbasis web.', 'https://example.com/article-1'),
('Dari Kitab Kuning ke Layar Kaca: Logika Pemrograman', 'article', 'Menemukan benang merah antara ilmu mantiq dan algoritma.', 'https://example.com/article-2'),
('Wawancara: Menyeimbangkan Ngaji dan Koding', 'podcast', 'Diskusi santai tentang membagi waktu antara hafalan dan deadline project.', 'https://example.com/podcast-1'),
('Peluang Karir IT untuk Lulusan Pesantren', 'podcast', 'Mengapa santri memiliki potensi besar di dunia teknologi modern.', 'https://example.com/podcast-2'),
('Tutorial Singkat: Membuat REST API Sederhana', 'video', 'Cara cepat membangun backend API untuk aplikasi mobile Anda.', 'https://example.com/video-1'),
('Vlog: Keseharian Santri Programmer', 'video', 'Melihat lebih dekat aktivitas saya dari subuh hingga malam hari di depan laptop.', 'https://example.com/video-2');
