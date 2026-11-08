<?php
/**
 * Initial Migration: create_initial_tables
 * 
 * Mencakup tabel-tabel utama sistem:
 * 1. contents
 * 2. subscribers
 * 3. presensi_halacoding
 * 4. users (tabel akun admin/pengguna baru)
 */

return new class {
    /**
     * Jalankan migrasi (Up)
     */
    public function up(PDO $pdo): void {
        $sql = "
            -- 1. Tabel untuk konten (artikel, podcast, video)
            CREATE TABLE IF NOT EXISTS `contents` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `title` VARCHAR(255) NOT NULL,
                `type` ENUM('article', 'podcast', 'video') NOT NULL,
                `excerpt` TEXT NOT NULL,
                `url` VARCHAR(255) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            -- 2. Tabel untuk subscribers newsletter
            CREATE TABLE IF NOT EXISTS `subscribers` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `email` VARCHAR(255) NOT NULL UNIQUE,
                `subscribed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            -- 3. Tabel untuk presensi peserta HalaCoding
            CREATE TABLE IF NOT EXISTS `presensi_halacoding` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `id_peserta` VARCHAR(50) NOT NULL UNIQUE,
                `nama` VARCHAR(150) NOT NULL,
                `whatsapp` VARCHAR(50) DEFAULT NULL,
                `instansi` VARCHAR(150) DEFAULT NULL,
                `track` VARCHAR(100) DEFAULT NULL,
                `waktu_presensi` VARCHAR(100) NOT NULL,
                `kritik_saran` TEXT DEFAULT NULL,
                `rating` INT DEFAULT 5,
                `status` VARCHAR(20) DEFAULT 'HADIR',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            -- 4. Tabel untuk akun admin/pengguna sistem (Users)
            CREATE TABLE IF NOT EXISTS `users` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(150) NOT NULL,
                `username` VARCHAR(50) NOT NULL UNIQUE,
                `email` VARCHAR(150) NOT NULL UNIQUE,
                `password` VARCHAR(255) NOT NULL,
                `role` VARCHAR(30) DEFAULT 'admin',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        $pdo->exec($sql);
    }

    /**
     * Batalkan migrasi (Down / Rollback)
     */
    public function down(PDO $pdo): void {
        $sql = "
            DROP TABLE IF EXISTS `users`;
            DROP TABLE IF EXISTS `presensi_halacoding`;
            DROP TABLE IF EXISTS `subscribers`;
            DROP TABLE IF EXISTS `contents`;
        ";

        $pdo->exec($sql);
    }
};

