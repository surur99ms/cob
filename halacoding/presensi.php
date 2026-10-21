<?php
/**
 * BACKEND PRESENSI HALACODING DENGAN DATABASE MYSQL
 * Menyimpan data kehadiran peserta langsung ke database server MySQL.
 * Dilengkapi fallback otomatis ke file JSON untuk kehandalan maksimal.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: no-cache, no-store, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$rekapFile = __DIR__ . '/rekap_presensi.json';

// Helper membaca data JSON (fallback/backup)
function getJsonData($filePath) {
    if (!file_exists($filePath)) return [];
    $raw = file_get_contents($filePath);
    if ($raw === false || trim($raw) === '') return [];
    $json = json_decode($raw, true);
    return is_array($json) ? $json : [];
}

// Helper menyimpan data JSON (fallback/backup)
function saveJsonData($filePath, $data) {
    $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($filePath, $encoded, LOCK_EX) !== false;
}

// Inisialisasi Koneksi Database MySQL
$pdo = null;
$useDatabase = false;

try {
    $host = 'localhost';
    $dbname = 'portfolio_db';
    $username = 'root';
    $password = '';

    // Gunakan konfigurasi dari ../koneksi.php jika ada
    if (file_exists(__DIR__ . '/../koneksi.php')) {
        @include_once __DIR__ . '/../koneksi.php';
    }

    if (!isset($pdo) || !$pdo) {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    // Pastikan tabel presensi_halacoding sudah tersedia
    $pdo->exec("CREATE TABLE IF NOT EXISTS presensi_halacoding (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_peserta VARCHAR(50) NOT NULL UNIQUE,
        nama VARCHAR(150) NOT NULL,
        whatsapp VARCHAR(50) DEFAULT NULL,
        instansi VARCHAR(150) DEFAULT NULL,
        track VARCHAR(100) DEFAULT NULL,
        waktu_presensi VARCHAR(100) NOT NULL,
        kritik_saran TEXT DEFAULT NULL,
        rating INT DEFAULT 5,
        status VARCHAR(20) DEFAULT 'HADIR',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $useDatabase = true;
} catch (PDOException $e) {
    // Jika koneksi MySQL gagal, sistem akan menggunakan file JSON sebagai fallback
    $useDatabase = false;
    error_log("Database connection failed in presensi.php: " . $e->getMessage());
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_rekap':
        if ($useDatabase) {
            try {
                $stmt = $pdo->query("SELECT 
                    id_peserta AS idPeserta,
                    nama,
                    whatsapp,
                    instansi,
                    track,
                    waktu_presensi AS timestamp,
                    kritik_saran AS kritikSaran,
                    rating,
                    status
                FROM presensi_halacoding 
                ORDER BY updated_at DESC");

                $records = $stmt->fetchAll();
                
                // Backup sinkron ke file JSON
                saveJsonData($rekapFile, $records);

                echo json_encode([
                    'status' => 'success',
                    'storage' => 'mysql_database',
                    'rekap' => $records,
                    'total' => count($records)
                ]);
                break;
            } catch (PDOException $e) {
                // Fallback ke JSON jika query gagal
            }
        }

        // Fallback JSON
        $data = getJsonData($rekapFile);
        echo json_encode([
            'status' => 'success',
            'storage' => 'json_backup',
            'rekap' => $data,
            'total' => count($data)
        ]);
        break;

    case 'submit_absen':
        $raw = file_get_contents('php://input');
        $record = json_decode($raw, true);

        if (!is_array($record) || empty($record['idPeserta'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Data absen tidak lengkap']);
            exit;
        }

        $idPeserta = trim($record['idPeserta']);
        $nama = trim($record['nama'] ?? '');
        $whatsapp = trim($record['whatsapp'] ?? ($record['email'] ?? '-'));
        $instansi = trim($record['instansi'] ?? '-');
        $track = trim($record['track'] ?? '-');
        $timestamp = trim($record['timestamp'] ?? date('d M Y, H:i'));
        $kritikSaran = trim($record['kritikSaran'] ?? 'Belum ada catatan kritik/saran.');
        $rating = intval($record['rating'] ?? 5);
        $status = trim($record['status'] ?? 'HADIR');

        $savedInDatabase = false;

        if ($useDatabase) {
            try {
                $sql = "INSERT INTO presensi_halacoding 
                    (id_peserta, nama, whatsapp, instansi, track, waktu_presensi, kritik_saran, rating, status)
                VALUES 
                    (:id_peserta, :nama, :whatsapp, :instansi, :track, :waktu_presensi, :kritik_saran, :rating, :status)
                ON DUPLICATE KEY UPDATE
                    nama = VALUES(nama),
                    whatsapp = VALUES(whatsapp),
                    instansi = VALUES(instansi),
                    track = VALUES(track),
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

        // Simpan juga ke JSON backup agar selalu sinkron
        $rekap = getJsonData($rekapFile);
        $existingIndex = -1;
        foreach ($rekap as $idx => $r) {
            if (strcasecmp($r['idPeserta'], $idPeserta) === 0) {
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

        // Hitung total dari database jika aktif
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

    case 'delete_absen':
        $id = trim($_GET['id'] ?? '');
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']);
            exit;
        }

        if ($useDatabase) {
            try {
                $stmt = $pdo->prepare("DELETE FROM presensi_halacoding WHERE LOWER(id_peserta) = LOWER(:id)");
                $stmt->execute([':id' => $id]);
            } catch (PDOException $e) {
                error_log("Gagal hapus di database: " . $e->getMessage());
            }
        }

        // Hapus dari JSON backup
        $rekap = getJsonData($rekapFile);
        $filtered = array_values(array_filter($rekap, function($r) use ($id) {
            return strcasecmp($r['idPeserta'], $id) !== 0;
        }));
        saveJsonData($rekapFile, $filtered);

        $totalCount = count($filtered);
        if ($useDatabase) {
            try {
                $totalCount = (int)$pdo->query("SELECT COUNT(*) FROM presensi_halacoding")->fetchColumn();
            } catch (Exception $e) {}
        }

        echo json_encode([
            'status' => 'success',
            'total' => $totalCount
        ]);
        break;

    case 'reset_rekap':
        if ($useDatabase) {
            try {
                $pdo->exec("TRUNCATE TABLE presensi_halacoding");
            } catch (PDOException $e) {
                error_log("Gagal reset database: " . $e->getMessage());
            }
        }

        saveJsonData($rekapFile, []);
        echo json_encode([
            'status' => 'success',
            'message' => 'Rekap presensi berhasil direset di database'
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Action tidak dikenali']);
        break;
}
