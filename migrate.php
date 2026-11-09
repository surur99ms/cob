<?php
/**
 * ============================================================================
 * Database Migration Runner
 * ============================================================================
 * Dibuat khusus untuk proyek Bos Surur oleh Pasep.
 * 
 * Penggunaan:
 *   php migrate.php          -> Menjalankan semua migrasi pending (Up)
 *   php migrate.php rollback -> Membatalkan batch migrasi terakhir (Down)
 *   php migrate.php status   -> Cek daftar status migrasi
 *   php migrate.php make [nama] -> Buat file migrasi baru dengan timestamp
 * ============================================================================
 */

// 1. Keamanan: Batasi HANYA bisa dieksekusi via CLI (Terminal / Cron Job Backend)
// Mencegah segala bentuk akses/eksekusi dari browser publik demi keamanan database 100%
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    die("Akses ditolak: File migrate.php hanya dapat dijalankan melalui CLI (Terminal / Cron Job).\n");
}

echo "=====================================================\n";
echo "           DATABASE MIGRATION RUNNER                 \n";
echo "=====================================================\n";

// 2. Memuat konfigurasi database secara dinamis dari koneksi.php atau config.php
$connectionFiles = [
    __DIR__ . '/koneksi.php',
    __DIR__ . '/config.php',
    __DIR__ . '/Koneksi.php',
    __DIR__ . '/Config.php',
];

$loadedFile = null;
foreach ($connectionFiles as $file) {
    if (file_exists($file)) {
        require_once $file;
        $loadedFile = basename($file);
        break;
    }
}

if (!$loadedFile) {
    fwrite(STDERR, "[ERROR] File koneksi (koneksi.php atau config.php) tidak ditemukan!\n");
    exit(1);
}

// 3. Pastikan koneksi PDO siap digunakan
if (!isset($pdo) || !($pdo instanceof PDO)) {
    if (isset($host, $dbname, $username)) {
        try {
            $pass = isset($password) ? $password : '';
            $charset = isset($charset) ? $charset : 'utf8mb4';
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            fwrite(STDERR, "[ERROR] Gagal terhubung ke database: " . $e->getMessage() . "\n");
            exit(1);
        }
    } else {
        fwrite(STDERR, "[ERROR] Variabel \$pdo atau kredensial database tidak ditemukan di $loadedFile!\n");
        exit(1);
    }
}

echo "[INFO] Berhasil terhubung ke database via $loadedFile\n";

// 4. Inisialisasi tabel tracking migrations jika belum ada
function initMigrationTable(PDO $pdo): void {
    $sql = "CREATE TABLE IF NOT EXISTS `migrations` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `migration` VARCHAR(255) NOT NULL UNIQUE,
        `batch` INT NOT NULL,
        `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql);
}

function getAppliedMap(PDO $pdo): array {
    $stmt = $pdo->query("SELECT `migration`, `batch`, `applied_at` FROM `migrations` ORDER BY `id` ASC");
    $map = [];
    while ($row = $stmt->fetch()) {
        $map[$row['migration']] = $row;
    }
    return $map;
}

function getLastBatch(PDO $pdo): int {
    $stmt = $pdo->query("SELECT MAX(`batch`) FROM `migrations`");
    $val = $stmt->fetchColumn();
    return $val !== null ? (int)$val : 0;
}

// 5. Lokasi folder migrasi
$migrationsDir = __DIR__ . '/database/migrations';
if (!is_dir($migrationsDir)) {
    mkdir($migrationsDir, 0755, true);
}

initMigrationTable($pdo);

// Ambil argument command CLI
$command = isset($argv[1]) ? strtolower(trim($argv[1])) : 'up';

switch ($command) {
    case 'up':
    case 'migrate':
        runMigrate($pdo, $migrationsDir);
        break;

    case 'down':
    case 'rollback':
        runRollback($pdo, $migrationsDir);
        break;

    case 'status':
        runStatus($pdo, $migrationsDir);
        break;

    case 'make':
        $name = isset($argv[2]) ? trim($argv[2]) : null;
        runMake($name, $migrationsDir);
        break;

    default:
        echo "Perintah tidak dikenal: $command\n\n";
        echo "Perintah yang dapat digunakan:\n";
        echo "  php migrate.php            -> Jalankan semua migrasi baru\n";
        echo "  php migrate.php rollback   -> Batalkan batch migrasi terakhir\n";
        echo "  php migrate.php status     -> Tampilkan daftar status migrasi\n";
        echo "  php migrate.php make [nama]-> Buat template migrasi baru\n";
        exit(1);
}

/**
 * Jalankan semua migrasi yang belum dieksekusi
 */
function runMigrate(PDO $pdo, string $dir): void {
    $applied = getAppliedMap($pdo);
    $files = glob($dir . '/*.php');
    sort($files);

    $pending = [];
    foreach ($files as $file) {
        $name = basename($file);
        if (!isset($applied[$name])) {
            $pending[] = $file;
        }
    }

    if (empty($pending)) {
        echo "[OK] Skema database sudah yang paling baru. Tidak ada migrasi pending.\n";
        return;
    }

    $nextBatch = getLastBatch($pdo) + 1;
    echo "[INFO] Menjalankan " . count($pending) . " migrasi baru (Batch $nextBatch)...\n";

    foreach ($pending as $file) {
        $name = basename($file);
        echo " -> Migrating: $name ... ";

        try {
            $migration = require $file;
            if (!is_object($migration) || !method_exists($migration, 'up')) {
                throw new Exception("File migrasi harus me-return class dengan method 'up(PDO \$pdo)'.");
            }

            $migration->up($pdo);

            $stmt = $pdo->prepare("INSERT INTO `migrations` (`migration`, `batch`) VALUES (:m, :b)");
            $stmt->execute([':m' => $name, ':b' => $nextBatch]);

            echo "[SUKSES]\n";
        } catch (Throwable $e) {
            echo "[GAGAL]\n";
            fwrite(STDERR, "\n[ERROR] Migrasi gagal pada file $name:\n" . $e->getMessage() . "\n");
            exit(1);
        }
    }

    echo "[SELESAI] Semua migrasi batch $nextBatch berhasil dieksekusi!\n";
}

/**
 * Batalkan migrasi batch terakhir
 */
function runRollback(PDO $pdo, string $dir): void {
    $lastBatch = getLastBatch($pdo);
    if ($lastBatch <= 0) {
        echo "[INFO] Tidak ada migrasi yang bisa di-rollback (tabel migrations kosong).\n";
        return;
    }

    $stmt = $pdo->prepare("SELECT `migration` FROM `migrations` WHERE `batch` = :b ORDER BY `id` DESC");
    $stmt->execute([':b' => $lastBatch]);
    $batchMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($batchMigrations)) {
        echo "[INFO] Tidak ada migrasi pada Batch $lastBatch.\n";
        return;
    }

    echo "[INFO] Membatalkan migrasi Batch $lastBatch (" . count($batchMigrations) . " file)...\n";

    foreach ($batchMigrations as $name) {
        $filePath = $dir . '/' . $name;
        echo " -> Rolling back: $name ... ";

        if (!file_exists($filePath)) {
            echo "[PERINGATAN: File fisik tidak ditemukan di $filePath]\n";
            $del = $pdo->prepare("DELETE FROM `migrations` WHERE `migration` = :m");
            $del->execute([':m' => $name]);
            continue;
        }

        try {
            $migration = require $filePath;
            if (is_object($migration) && method_exists($migration, 'down')) {
                $migration->down($pdo);
            }

            $del = $pdo->prepare("DELETE FROM `migrations` WHERE `migration` = :m");
            $del->execute([':m' => $name]);

            echo "[SUKSES]\n";
        } catch (Throwable $e) {
            echo "[GAGAL]\n";
            fwrite(STDERR, "\n[ERROR] Rollback gagal pada $name:\n" . $e->getMessage() . "\n");
            exit(1);
        }
    }

    echo "[SELESAI] Rollback Batch $lastBatch selesai!\n";
}

/**
 * Tampilkan tabel status seluruh migrasi
 */
function runStatus(PDO $pdo, string $dir): void {
    $applied = getAppliedMap($pdo);
    $files = glob($dir . '/*.php');
    sort($files);

    echo sprintf("\n%-50s | %-8s | %-6s | %s\n", "Nama File Migrasi", "Status", "Batch", "Diterapkan Pada");
    echo str_repeat("-", 88) . "\n";

    if (empty($files) && empty($applied)) {
        echo "Belum ada file migrasi yang ditemukan.\n";
        return;
    }

    // Gabungkan file fisik dan database
    $allNames = [];
    foreach ($files as $f) {
        $allNames[basename($f)] = true;
    }
    foreach (array_keys($applied) as $m) {
        $allNames[$m] = true;
    }
    ksort($allNames);

    foreach (array_keys($allNames) as $name) {
        if (isset($applied[$name])) {
            $status = "OK";
            $batch = (string)$applied[$name]['batch'];
            $date = $applied[$name]['applied_at'];
        } else {
            $status = "PENDING";
            $batch = "-";
            $date = "-";
        }
        echo sprintf("%-50s | %-8s | %-6s | %s\n", $name, $status, $batch, $date);
    }
    echo "\n";
}

/**
 * Generator file migrasi baru
 */
function runMake(?string $name, string $dir): void {
    if (!$name) {
        fwrite(STDERR, "[ERROR] Mohon masukkan nama migrasi. Contoh: php migrate.php make create_posts_table\n");
        exit(1);
    }

    $cleanName = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($name));
    $cleanName = trim($cleanName, '_');
    $timestamp = date('Y_m_d_His');
    $filename = "{$timestamp}_{$cleanName}.php";
    $filepath = $dir . '/' . $filename;

    $template = <<<PHP
<?php
/**
 * Migration: {$cleanName}
 * Created at: {$timestamp}
 */

return new class {
    /**
     * Jalankan perubahan skema database
     */
    public function up(PDO \$pdo): void {
        \$sql = "
            -- Tuliskan query DDL di sini, contoh:
            -- CREATE TABLE IF NOT EXISTS `contoh` (
            --     `id` INT AUTO_INCREMENT PRIMARY KEY,
            --     `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            -- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        \$pdo->exec(\$sql);
    }

    /**
     * Batalkan perubahan skema database
     */
    public function down(PDO \$pdo): void {
        \$sql = "
            -- Tuliskan query pembatalan di sini, contoh:
            -- DROP TABLE IF EXISTS `contoh`;
        ";
        \$pdo->exec(\$sql);
    }
};
PHP;

    file_put_contents($filepath, $template);
    echo "[SUKSES] File migrasi berhasil dibuat:\n";
    echo " -> database/migrations/{$filename}\n";
}

