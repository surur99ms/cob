<?php
/**
 * BACKEND PRESENSI HALACODING DENGAN DUKUNGAN BATCH & 4 PERTEMUAN
 * Corefive / Halacoding
 * 
 * Fitur:
 * - Multi-Batch (Generasi 1 / Batch 1, Batch 2, dst)
 * - 4 Pertemuan per Batch (Pertemuan 1, 2, 3, 4)
 * - Penyimpanan MySQL Server dengan fallback sinkron ke JSON
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: no-cache, no-store, must-revalidate');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$rekapFile = __DIR__ . '/rekap_presensi.json';
$settingsFile = __DIR__ . '/session_settings.json';

// Helper membaca data JSON
function getJsonData($filePath) {
    if (!file_exists($filePath)) return [];
    $raw = file_get_contents($filePath);
    if ($raw === false || trim($raw) === '') return [];
    $json = json_decode($raw, true);
    return is_array($json) ? $json : [];
}

// Helper menyimpan data JSON
function saveJsonData($filePath, $data) {
    $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $saved = @file_put_contents($filePath, $encoded, LOCK_EX);
    if ($saved === false) {
        $saved = @file_put_contents($filePath, $encoded);
    }
    if ($saved !== false) {
        @chmod($filePath, 0666);
    }
    return $saved !== false;
}

// Inisialisasi Koneksi Database MySQL
$pdo = null;
$useDatabase = false;
$dbError = null;

$host = 'localhost';
$dbname = 'portfolio_db';
$username = 'root';
$password = '';

// Baca kredensial dari ../koneksi.php secara aman
if (file_exists(__DIR__ . '/../koneksi.php')) {
    $koneksiRaw = @file_get_contents(__DIR__ . '/../koneksi.php');
    if ($koneksiRaw) {
        if (preg_match('/\$host\s*=\s*[\'"]([^\'"]+)[\'"]\s*;/', $koneksiRaw, $m)) $host = $m[1];
        if (preg_match('/\$dbname\s*=\s*[\'"]([^\'"]+)[\'"]\s*;/', $koneksiRaw, $m)) $dbname = $m[1];
        if (preg_match('/\$username\s*=\s*[\'"]([^\'"]+)[\'"]\s*;/', $koneksiRaw, $m)) $username = $m[1];
        if (preg_match('/\$password\s*=\s*[\'"]([^\'"]*)[\'"]\s*;/', $koneksiRaw, $m)) $password = $m[1];
    }
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 3
    ]);

    // 1. Pastikan tabel presensi_halacoding sudah ada
    $pdo->exec("CREATE TABLE IF NOT EXISTS presensi_halacoding (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_peserta VARCHAR(50) NOT NULL,
        nama VARCHAR(150) NOT NULL,
        whatsapp VARCHAR(50) DEFAULT NULL,
        instansi VARCHAR(150) DEFAULT NULL,
        track VARCHAR(100) DEFAULT NULL,
        batch VARCHAR(50) NOT NULL DEFAULT 'Batch 1',
        pertemuan INT NOT NULL DEFAULT 1,
        materi VARCHAR(255) DEFAULT NULL,
        waktu_presensi VARCHAR(100) NOT NULL,
        kritik_saran TEXT DEFAULT NULL,
        rating INT DEFAULT 5,
        status VARCHAR(20) DEFAULT 'HADIR',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 2. Upgrade kolom jika tabel sebelumnya masih skema lama
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

    // 3. Lepaskan unique index lama pada id_peserta
    try {
        $indexes = $pdo->query("SHOW INDEX FROM `presensi_halacoding` WHERE Column_name = 'id_peserta' AND Non_unique = 0")->fetchAll();
        foreach ($indexes as $idx) {
            $keyName = $idx['Key_name'];
            if ($keyName !== 'PRIMARY' && $keyName !== 'unique_peserta_batch_pertemuan') {
                $pdo->exec("ALTER TABLE `presensi_halacoding` DROP INDEX `$keyName`");
            }
        }
    } catch (Throwable $e) {}

    // 4. Buat composite unique index (id_peserta, batch, pertemuan)
    try {
        $checkUnique = $pdo->query("SHOW INDEX FROM `presensi_halacoding` WHERE Key_name = 'unique_peserta_batch_pertemuan'")->fetch();
        if (!$checkUnique) {
            $pdo->exec("ALTER TABLE `presensi_halacoding` ADD UNIQUE KEY `unique_peserta_batch_pertemuan` (`id_peserta`, `batch`, `pertemuan`)");
        }
    } catch (Throwable $e) {}

    // 5. Tabel batch manajemen
    $pdo->exec("CREATE TABLE IF NOT EXISTS `halacoding_batches` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `batch_name` VARCHAR(100) NOT NULL UNIQUE,
        `is_active` TINYINT(1) DEFAULT 1,
        `active_pertemuan` INT DEFAULT 1,
        `keterangan` VARCHAR(255) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $pdo->exec("INSERT INTO `halacoding_batches` (`batch_name`, `is_active`, `active_pertemuan`, `keterangan`)
        SELECT 'Batch 1', 1, 1, 'Pelatihan Generasi 1 (Batch 1)'
        WHERE NOT EXISTS (SELECT 1 FROM `halacoding_batches` WHERE `batch_name` = 'Batch 1');");

    $useDatabase = true;
} catch (Throwable $e) {
    $useDatabase = false;
    $dbError = $e->getMessage();
}

$action = $_GET['action'] ?? '';

// Helper membaca session setting aktif (Batch aktif & Pertemuan aktif)
function getActiveSession($pdo, $useDatabase, $settingsFile) {
    if ($useDatabase) {
        try {
            $stmt = $pdo->query("SELECT `batch_name`, `active_pertemuan` FROM `halacoding_batches` WHERE `is_active` = 1 ORDER BY `id` DESC LIMIT 1");
            $row = $stmt->fetch();
            if ($row) {
                return [
                    'active_batch' => $row['batch_name'],
                    'active_pertemuan' => (int)$row['active_pertemuan']
                ];
            }
        } catch (Throwable $e) {}
    }
    // Fallback JSON setting
    $settings = getJsonData($settingsFile);
    return [
        'active_batch' => $settings['active_batch'] ?? 'Batch 1',
        'active_pertemuan' => (int)($settings['active_pertemuan'] ?? 1)
    ];
}

switch ($action) {
    // ----------------------------------------------------
    // GET: Ambil seluruh rekap data + info batch & pertemuan
    // ----------------------------------------------------
    case 'get_rekap':
        $filterBatch = trim($_GET['batch'] ?? 'all');
        $filterPertemuan = trim($_GET['pertemuan'] ?? 'all');

        $session = getActiveSession($pdo, $useDatabase, $settingsFile);
        $records = [];
        $batchesList = ['Batch 1'];

        if ($useDatabase) {
            try {
                // Ambil daftar batch yang ada
                $batchRows = $pdo->query("SELECT DISTINCT `batch_name` FROM `halacoding_batches` ORDER BY `id` ASC")->fetchAll(PDO::FETCH_COLUMN);
                if (!empty($batchRows)) {
                    $batchesList = $batchRows;
                }

                // Ambil juga jika ada batch di tabel presensi yang belum terdaftar di tabel master
                $presensiBatches = $pdo->query("SELECT DISTINCT `batch` FROM `presensi_halacoding` WHERE `batch` IS NOT NULL AND `batch` != ''")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($presensiBatches as $pb) {
                    if (!in_array($pb, $batchesList)) {
                        $batchesList[] = $pb;
                    }
                }

                // Query data presensi
                $sql = "SELECT 
                    id,
                    id_peserta AS idPeserta,
                    nama,
                    whatsapp,
                    instansi,
                    track,
                    batch,
                    pertemuan,
                    materi,
                    waktu_presensi AS timestamp,
                    kritik_saran AS kritikSaran,
                    rating,
                    status
                FROM presensi_halacoding 
                WHERE 1=1";
                $params = [];

                if ($filterBatch !== 'all' && $filterBatch !== '') {
                    $sql .= " AND `batch` = :batch";
                    $params[':batch'] = $filterBatch;
                }

                if ($filterPertemuan !== 'all' && $filterPertemuan !== '') {
                    $sql .= " AND `pertemuan` = :pertemuan";
                    $params[':pertemuan'] = (int)$filterPertemuan;
                }

                $sql .= " ORDER BY updated_at DESC";

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $records = $stmt->fetchAll();

                // Hitung statistik per pertemuan untuk batch yang dipilih (atau batch aktif jika 'all')
                $targetBatchForStats = ($filterBatch !== 'all' && $filterBatch !== '') ? $filterBatch : $session['active_batch'];
                $statStmt = $pdo->prepare("SELECT 
                    `pertemuan`, COUNT(*) as total_hadir 
                FROM `presensi_halacoding` 
                WHERE `batch` = :b 
                GROUP BY `pertemuan`");
                $statStmt->execute([':b' => $targetBatchForStats]);
                $pertemuanStats = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
                while ($s = $statStmt->fetch()) {
                    $pNum = (int)$s['pertemuan'];
                    if (isset($pertemuanStats[$pNum])) {
                        $pertemuanStats[$pNum] = (int)$s['total_hadir'];
                    }
                }

                // Backup sinkron ke file JSON
                saveJsonData($rekapFile, $records);

                echo json_encode([
                    'status' => 'success',
                    'storage' => 'mysql_database',
                    'rekap' => $records,
                    'total' => count($records),
                    'batches' => array_values(array_unique($batchesList)),
                    'active_batch' => $session['active_batch'],
                    'active_pertemuan' => $session['active_pertemuan'],
                    'pertemuan_stats' => $pertemuanStats
                ]);
                break;
            } catch (PDOException $e) {
                // Fallback ke JSON jika query DB error
            }
        }

        // Fallback JSON storage
        $allJson = getJsonData($rekapFile);
        $filtered = array_values(array_filter($allJson, function($item) use ($filterBatch, $filterPertemuan) {
            $matchBatch = ($filterBatch === 'all' || !isset($item['batch']) || strcasecmp($item['batch'], $filterBatch) === 0);
            $matchPertemuan = ($filterPertemuan === 'all' || !isset($item['pertemuan']) || (int)$item['pertemuan'] === (int)$filterPertemuan);
            return $matchBatch && $matchPertemuan;
        }));

        echo json_encode([
            'status' => 'success',
            'storage' => 'json_backup',
            'rekap' => $filtered,
            'total' => count($filtered),
            'batches' => $batchesList,
            'active_batch' => $session['active_batch'],
            'active_pertemuan' => $session['active_pertemuan'],
            'pertemuan_stats' => [1 => 0, 2 => 0, 3 => 0, 4 => 0]
        ]);
        break;

    // ----------------------------------------------------
    // POST: Simpan presensi peserta (Submit Absen)
    // ----------------------------------------------------
    case 'submit_absen':
        $raw = file_get_contents('php://input');
        $record = json_decode($raw, true);

        if (!is_array($record) || empty($record['idPeserta'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Data absen tidak lengkap']);
            exit;
        }

        $session = getActiveSession($pdo, $useDatabase, $settingsFile);

        $idPeserta = trim($record['idPeserta']);
        $nama = trim($record['nama'] ?? '');
        $whatsapp = trim($record['whatsapp'] ?? ($record['email'] ?? '-'));
        $instansi = trim($record['instansi'] ?? '-');
        $track = trim($record['track'] ?? '-');
        $batch = trim($record['batch'] ?? $session['active_batch']);
        if ($batch === '') $batch = 'Batch 1';
        $pertemuan = intval($record['pertemuan'] ?? $session['active_pertemuan']);
        if ($pertemuan < 1 || $pertemuan > 4) $pertemuan = 1;
        $materi = trim($record['materi'] ?? "Pertemuan $pertemuan");
        $timestamp = trim($record['timestamp'] ?? date('d M Y, H:i'));
        $kritikSaran = trim($record['kritikSaran'] ?? 'Belum ada catatan kritik/saran.');
        $rating = intval($record['rating'] ?? 5);
        $status = trim($record['status'] ?? 'HADIR');

        $savedInDatabase = false;

        if ($useDatabase) {
            try {
                $sql = "INSERT INTO presensi_halacoding 
                    (id_peserta, nama, whatsapp, instansi, track, batch, pertemuan, materi, waktu_presensi, kritik_saran, rating, status)
                VALUES 
                    (:id_peserta, :nama, :whatsapp, :instansi, :track, :batch, :pertemuan, :materi, :waktu_presensi, :kritik_saran, :rating, :status)
                ON DUPLICATE KEY UPDATE
                    nama = VALUES(nama),
                    whatsapp = VALUES(whatsapp),
                    instansi = VALUES(instansi),
                    track = VALUES(track),
                    materi = VALUES(materi),
                    waktu_presensi = VALUES(waktu_presensi),
                    kritik_saran = VALUES(kritik_saran),
                    rating = VALUES(rating),
                    status = VALUES(status),
                    updated_at = CURRENT_TIMESTAMP";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':id_peserta' => $idPeserta,
                    ':nama' => $nama,
                    ':whatsapp' => $whatsapp,
                    ':instansi' => $instansi,
                    ':track' => $track,
                    ':batch' => $batch,
                    ':pertemuan' => $pertemuan,
                    ':materi' => $materi,
                    ':waktu_presensi' => $timestamp,
                    ':kritik_saran' => $kritikSaran,
                    ':rating' => $rating,
                    ':status' => $status
                ]);

                $savedInDatabase = true;
            } catch (PDOException $e) {
                error_log("Gagal simpan ke database: " . $e->getMessage());
            }
        }

        // Sinkronisasi ke JSON backup
        $rekap = getJsonData($rekapFile);
        $existingIndex = -1;
        foreach ($rekap as $idx => $r) {
            $rBatch = $r['batch'] ?? 'Batch 1';
            $rPertemuan = (int)($r['pertemuan'] ?? 1);
            if (strcasecmp($r['idPeserta'], $idPeserta) === 0 && strcasecmp($rBatch, $batch) === 0 && $rPertemuan === $pertemuan) {
                $existingIndex = $idx;
                break;
            }
        }

        $formattedRecord = [
            'idPeserta' => $idPeserta,
            'nama' => $nama,
            'whatsapp' => $whatsapp,
            'instansi' => $instansi,
            'track' => $track,
            'batch' => $batch,
            'pertemuan' => $pertemuan,
            'materi' => $materi,
            'timestamp' => $timestamp,
            'kritikSaran' => $kritikSaran,
            'rating' => $rating,
            'status' => $status
        ];

        if ($existingIndex !== -1) {
            $rekap[$existingIndex] = $formattedRecord;
        } else {
            array_unshift($rekap, $formattedRecord);
        }
        saveJsonData($rekapFile, $rekap);

        $totalCount = count($rekap);
        if ($useDatabase && $savedInDatabase) {
            try {
                $totalCount = (int)$pdo->query("SELECT COUNT(*) FROM presensi_halacoding")->fetchColumn();
            } catch (Exception $e) {}
        }

        echo json_encode([
            'status' => 'success',
            'storage' => $savedInDatabase ? 'mysql_database' : 'json_backup',
            'record' => $formattedRecord,
            'total' => $totalCount
        ]);
        break;

    // ----------------------------------------------------
    // POST: Set Sesi Aktif (Batch Aktif & Pertemuan Aktif)
    // ----------------------------------------------------
    case 'set_active_session':
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);

        $targetBatch = trim($input['batch'] ?? 'Batch 1');
        $targetPertemuan = intval($input['pertemuan'] ?? 1);
        if ($targetPertemuan < 1 || $targetPertemuan > 4) $targetPertemuan = 1;

        if ($useDatabase) {
            try {
                // Nonaktifkan batch lain
                $pdo->exec("UPDATE `halacoding_batches` SET `is_active` = 0");

                // Aktifkan batch yang dipilih dan pertemuannya
                $stmt = $pdo->prepare("INSERT INTO `halacoding_batches` (`batch_name`, `is_active`, `active_pertemuan`) 
                    VALUES (:b, 1, :p)
                    ON DUPLICATE KEY UPDATE `is_active` = 1, `active_pertemuan` = VALUES(`active_pertemuan`)");
                $stmt->execute([':b' => $targetBatch, ':p' => $targetPertemuan]);
            } catch (PDOException $e) {
                error_log("Gagal set active session di DB: " . $e->getMessage());
            }
        }

        saveJsonData($settingsFile, [
            'active_batch' => $targetBatch,
            'active_pertemuan' => $targetPertemuan
        ]);

        echo json_encode([
            'status' => 'success',
            'active_batch' => $targetBatch,
            'active_pertemuan' => $targetPertemuan,
            'message' => "Sesi presensi aktif disetel ke $targetBatch - Pertemuan $targetPertemuan"
        ]);
        break;

    // ----------------------------------------------------
    // POST: Tambah Batch Baru
    // ----------------------------------------------------
    case 'add_batch':
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);
        $newBatch = trim($input['batch_name'] ?? '');

        if ($newBatch === '') {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Nama Batch tidak boleh kosong']);
            exit;
        }

        if ($useDatabase) {
            try {
                $stmt = $pdo->prepare("INSERT IGNORE INTO `halacoding_batches` (`batch_name`, `is_active`, `active_pertemuan`, `keterangan`) 
                    VALUES (:b, 0, 1, :ket)");
                $stmt->execute([':b' => $newBatch, ':ket' => "Pelatihan $newBatch"]);
            } catch (PDOException $e) {}
        }

        echo json_encode([
            'status' => 'success',
            'batch_name' => $newBatch,
            'message' => "Batch $newBatch berhasil ditambahkan"
        ]);
        break;

    // ----------------------------------------------------
    // POST/GET: Hapus presensi peserta tertentu
    // ----------------------------------------------------
    case 'delete_absen':
        $idPeserta = trim($_GET['id'] ?? ($_POST['idPeserta'] ?? ''));
        $batch = trim($_GET['batch'] ?? ($_POST['batch'] ?? ''));
        $pertemuan = isset($_GET['pertemuan']) ? intval($_GET['pertemuan']) : (isset($_POST['pertemuan']) ? intval($_POST['pertemuan']) : null);

        if (!$idPeserta) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID peserta tidak valid']);
            exit;
        }

        if ($useDatabase) {
            try {
                $sql = "DELETE FROM presensi_halacoding WHERE LOWER(id_peserta) = LOWER(:id)";
                $params = [':id' => $idPeserta];

                if ($batch !== '') {
                    $sql .= " AND `batch` = :batch";
                    $params[':batch'] = $batch;
                }
                if ($pertemuan !== null && $pertemuan > 0) {
                    $sql .= " AND `pertemuan` = :pertemuan";
                    $params[':pertemuan'] = $pertemuan;
                }

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            } catch (PDOException $e) {
                error_log("Gagal hapus di database: " . $e->getMessage());
            }
        }

        // Hapus dari JSON backup
        $rekap = getJsonData($rekapFile);
        $filtered = array_values(array_filter($rekap, function($r) use ($idPeserta, $batch, $pertemuan) {
            $matchId = (strcasecmp($r['idPeserta'], $idPeserta) === 0);
            if (!$matchId) return true;
            if ($batch !== '' && isset($r['batch']) && strcasecmp($r['batch'], $batch) !== 0) return true;
            if ($pertemuan !== null && isset($r['pertemuan']) && (int)$r['pertemuan'] !== $pertemuan) return true;
            return false;
        }));
        saveJsonData($rekapFile, $filtered);

        echo json_encode([
            'status' => 'success',
            'total' => count($filtered)
        ]);
        break;

    // ----------------------------------------------------
    // POST: Reset Rekap
    // ----------------------------------------------------
    case 'reset_rekap':
        $targetBatch = trim($_GET['batch'] ?? ($_POST['batch'] ?? 'all'));

        if ($useDatabase) {
            try {
                if ($targetBatch === 'all') {
                    $pdo->exec("TRUNCATE TABLE presensi_halacoding");
                } else {
                    $stmt = $pdo->prepare("DELETE FROM presensi_halacoding WHERE `batch` = :b");
                    $stmt->execute([':b' => $targetBatch]);
                }
            } catch (PDOException $e) {
                error_log("Gagal reset database: " . $e->getMessage());
            }
        }

        if ($targetBatch === 'all') {
            saveJsonData($rekapFile, []);
        } else {
            $rekap = getJsonData($rekapFile);
            $rekap = array_values(array_filter($rekap, function($r) use ($targetBatch) {
                return strcasecmp($r['batch'] ?? 'Batch 1', $targetBatch) !== 0;
            }));
            saveJsonData($rekapFile, $rekap);
        }

        echo json_encode([
            'status' => 'success',
            'message' => ($targetBatch === 'all') ? 'Seluruh rekap presensi berhasil direset' : "Rekap presensi $targetBatch berhasil direset"
        ]);
        break;

    case 'status':
        $jsonWritable = is_writable(__DIR__) || (file_exists($rekapFile) && is_writable($rekapFile));
        $session = getActiveSession($pdo, $useDatabase, $settingsFile);
        echo json_encode([
            'status' => 'success',
            'storage' => $useDatabase ? 'mysql_database' : 'json_backup',
            'mysql_connected' => $useDatabase,
            'mysql_error' => $dbError,
            'active_batch' => $session['active_batch'],
            'active_pertemuan' => $session['active_pertemuan'],
            'json_file_exists' => file_exists($rekapFile),
            'json_writable' => $jsonWritable,
            'total_in_db' => $useDatabase ? (int)$pdo->query("SELECT COUNT(*) FROM presensi_halacoding")->fetchColumn() : null,
            'total_in_json' => count(getJsonData($rekapFile))
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Action tidak dikenali']);
        break;
}
