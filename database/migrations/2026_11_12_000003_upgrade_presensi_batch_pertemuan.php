<?php
/**
 * Migration: upgrade_presensi_batch_pertemuan
 * 
 * Menambahkan dukungan Batch / Generasi pelatihan dan 4 Pertemuan per Batch
 * pada sistem presensi Halacoding.
 */

return new class {
    /**
     * Jalankan migrasi (Up)
     */
    public function up(PDO $pdo): void {
        // 1. Buat tabel master batches jika belum ada
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `halacoding_batches` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `batch_name` VARCHAR(100) NOT NULL UNIQUE,
                `is_active` TINYINT(1) DEFAULT 1,
                `active_pertemuan` INT DEFAULT 1,
                `keterangan` VARCHAR(255) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Masukkan data awal Batch 1 jika belum ada
        $pdo->exec("
            INSERT INTO `halacoding_batches` (`batch_name`, `is_active`, `active_pertemuan`, `keterangan`)
            SELECT 'Batch 1', 1, 1, 'Pelatihan Generasi 1 (Batch 1)'
            WHERE NOT EXISTS (SELECT 1 FROM `halacoding_batches` WHERE `batch_name` = 'Batch 1');
        ");

        // 2. Periksa dan tambahkan kolom 'batch', 'pertemuan', 'materi' ke tabel presensi_halacoding
        try {
            $colBatch = $pdo->query("SHOW COLUMNS FROM `presensi_halacoding` LIKE 'batch'")->fetch();
            if (!$colBatch) {
                $pdo->exec("ALTER TABLE `presensi_halacoding` ADD COLUMN `batch` VARCHAR(50) NOT NULL DEFAULT 'Batch 1' AFTER `track`");
            }
        } catch (Throwable $e) {}

        try {
            $colPertemuan = $pdo->query("SHOW COLUMNS FROM `presensi_halacoding` LIKE 'pertemuan'")->fetch();
            if (!$colPertemuan) {
                $pdo->exec("ALTER TABLE `presensi_halacoding` ADD COLUMN `pertemuan` INT NOT NULL DEFAULT 1 AFTER `batch`");
            }
        } catch (Throwable $e) {}

        try {
            $colMateri = $pdo->query("SHOW COLUMNS FROM `presensi_halacoding` LIKE 'materi'")->fetch();
            if (!$colMateri) {
                $pdo->exec("ALTER TABLE `presensi_halacoding` ADD COLUMN `materi` VARCHAR(255) DEFAULT NULL AFTER `pertemuan`");
            }
        } catch (Throwable $e) {}

        // 3. Lepaskan unique index lama pada id_peserta agar 1 peserta bisa presensi di 4 pertemuan berbeda
        try {
            // Cari nama index UNIQUE yang ada pada id_peserta
            $indexes = $pdo->query("SHOW INDEX FROM `presensi_halacoding` WHERE Column_name = 'id_peserta' AND Non_unique = 0")->fetchAll();
            foreach ($indexes as $idx) {
                $keyName = $idx['Key_name'];
                if ($keyName !== 'PRIMARY' && $keyName !== 'unique_peserta_batch_pertemuan') {
                    $pdo->exec("ALTER TABLE `presensi_halacoding` DROP INDEX `$keyName`");
                }
            }
        } catch (Throwable $e) {}

        // 4. Tambahkan composite unique index: 1 peserta hanya 1 kali presensi per pertemuan di batch yang sama
        try {
            $checkUnique = $pdo->query("SHOW INDEX FROM `presensi_halacoding` WHERE Key_name = 'unique_peserta_batch_pertemuan'")->fetch();
            if (!$checkUnique) {
                $pdo->exec("ALTER TABLE `presensi_halacoding` ADD UNIQUE KEY `unique_peserta_batch_pertemuan` (`id_peserta`, `batch`, `pertemuan`)");
            }
        } catch (Throwable $e) {}
    }

    /**
     * Batalkan migrasi (Down / Rollback)
     */
    public function down(PDO $pdo): void {
        try {
            $pdo->exec("ALTER TABLE `presensi_halacoding` DROP INDEX `unique_peserta_batch_pertemuan`");
        } catch (Throwable $e) {}

        try {
            $pdo->exec("DROP TABLE IF EXISTS `halacoding_batches`");
        } catch (Throwable $e) {}
    }
};
