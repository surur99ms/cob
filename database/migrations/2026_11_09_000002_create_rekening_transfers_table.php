<?php
/**
 * Migration: create_rekening_transfers_table
 * Dibuat khusus untuk aplikasi rekap transfer dan salin nomor rekening Bos Surur.
 */

return new class {
    /**
     * Jalankan migrasi skema tabel
     */
    public function up(PDO $pdo): void {
        $sql = "
            CREATE TABLE IF NOT EXISTS `rekening_transfers` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `nama` VARCHAR(150) NOT NULL,
                `wilayah` VARCHAR(50) DEFAULT '-',
                `nominal` DECIMAL(15, 2) NOT NULL DEFAULT 0,
                `rekening` VARCHAR(50) NOT NULL,
                `status_tf` VARCHAR(20) DEFAULT 'BELUM',
                `keterangan` TEXT DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            -- Masukkan data awal sampel sesuai gambar Bos Surur jika tabel baru saja dibuat
            INSERT INTO `rekening_transfers` (`nama`, `wilayah`, `nominal`, `rekening`, `status_tf`)
            SELECT 'ABD MAJID', 'J-1', 272750, '17451201715123', 'SUDAH'
            WHERE NOT EXISTS (SELECT 1 FROM `rekening_transfers` LIMIT 1);

            INSERT INTO `rekening_transfers` (`nama`, `wilayah`, `nominal`, `rekening`, `status_tf`)
            SELECT 'ABD WAHID', 'J-1', 154950, '9881230208204356', 'SUDAH'
            WHERE (SELECT COUNT(*) FROM `rekening_transfers`) = 1;

            INSERT INTO `rekening_transfers` (`nama`, `wilayah`, `nominal`, `rekening`, `status_tf`)
            SELECT 'AHMAD IDRIS', 'J-1', 110550, '17453742019647', 'SUDAH'
            WHERE (SELECT COUNT(*) FROM `rekening_transfers`) = 2;

            INSERT INTO `rekening_transfers` (`nama`, `wilayah`, `nominal`, `rekening`, `status_tf`)
            SELECT 'AHMAD ZAINI', 'J-1', 335100, '115810018', 'SUDAH'
            WHERE (SELECT COUNT(*) FROM `rekening_transfers`) = 3;
        ";

        $pdo->exec($sql);
    }

    /**
     * Batalkan migrasi (Rollback)
     */
    public function down(PDO $pdo): void {
        $sql = "DROP TABLE IF EXISTS `rekening_transfers`;";
        $pdo->exec($sql);
    }
};

