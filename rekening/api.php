<?php
/**
 * ============================================================
 * Backend API: Rekap Transfer & Salin Rekening
 * Corefive / SantriKoding
 * ============================================================
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 1. Memuat koneksi database
$koneksiFile = __DIR__ . '/../koneksi.php';
if (!file_exists($koneksiFile)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'File koneksi.php tidak ditemukan']);
    exit;
}

require_once $koneksiFile;

if (!isset($pdo) || !($pdo instanceof PDO)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Koneksi database PDO gagal']);
    exit;
}

// Pastikan tabel rekening_transfers tersedia
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `rekening_transfers` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nama` VARCHAR(150) NOT NULL,
        `jabatan` VARCHAR(100) DEFAULT '-',
        `wilayah` VARCHAR(50) DEFAULT '-',
        `nominal` DECIMAL(15, 2) NOT NULL DEFAULT 0,
        `rekening` VARCHAR(50) NOT NULL,
        `status_tf` VARCHAR(20) DEFAULT 'BELUM',
        `keterangan` TEXT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// Pastikan kolom jabatan tersedia jika tabel sudah pernah dibuat sebelumnya
try {
    $checkCol = $pdo->query("SHOW COLUMNS FROM `rekening_transfers` LIKE 'jabatan'")->fetch();
    if (!$checkCol) {
        $pdo->exec("ALTER TABLE `rekening_transfers` ADD COLUMN `jabatan` VARCHAR(100) DEFAULT '-' AFTER `nama`");
    }
} catch (Throwable $e) {}

$action = $_GET['action'] ?? 'list';

switch ($action) {
    // ----------------------------------------------------
    // GET: Ambil daftar data + statistik
    // ----------------------------------------------------
    case 'list':
        try {
            $search = trim($_GET['q'] ?? '');
            $filterStatus = trim($_GET['status'] ?? 'all');
            $filterWilayah = trim($_GET['wilayah'] ?? 'all');
            $filterJabatan = trim($_GET['jabatan'] ?? 'all');

            $sql = "SELECT * FROM `rekening_transfers` WHERE 1=1";
            $params = [];

            if ($search !== '') {
                $sql .= " AND (`nama` LIKE :q OR `jabatan` LIKE :q OR `rekening` LIKE :q OR `wilayah` LIKE :q)";
                $params[':q'] = "%$search%";
            }

            if ($filterStatus !== 'all' && $filterStatus !== '') {
                $sql .= " AND `status_tf` = :status";
                $params[':status'] = $filterStatus;
            }

            if ($filterWilayah !== 'all' && $filterWilayah !== '') {
                $sql .= " AND `wilayah` = :wilayah";
                $params[':wilayah'] = $filterWilayah;
            }

            if ($filterJabatan !== 'all' && $filterJabatan !== '') {
                $sql .= " AND `jabatan` = :jabatan";
                $params[':jabatan'] = $filterJabatan;
            }

            $sql .= " ORDER BY `id` ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Hitung statistik
            $statStmt = $pdo->query("SELECT 
                COUNT(*) as total_items,
                COALESCE(SUM(nominal), 0) as total_nominal,
                COALESCE(SUM(CASE WHEN UPPER(status_tf) = 'SUDAH' THEN 1 ELSE 0 END), 0) as count_sudah,
                COALESCE(SUM(CASE WHEN UPPER(status_tf) = 'SUDAH' THEN nominal ELSE 0 END), 0) as nominal_sudah,
                COALESCE(SUM(CASE WHEN UPPER(status_tf) != 'SUDAH' THEN 1 ELSE 0 END), 0) as count_belum,
                COALESCE(SUM(CASE WHEN UPPER(status_tf) != 'SUDAH' THEN nominal ELSE 0 END), 0) as nominal_belum
            FROM `rekening_transfers`");
            $stats = $statStmt->fetch(PDO::FETCH_ASSOC);

            // Ambil daftar wilayah unik untuk filter
            $wilayahList = $pdo->query("SELECT DISTINCT `wilayah` FROM `rekening_transfers` WHERE `wilayah` IS NOT NULL AND `wilayah` != '' ORDER BY `wilayah` ASC")->fetchAll(PDO::FETCH_COLUMN);

            // Ambil daftar jabatan unik untuk filter
            $jabatanList = $pdo->query("SELECT DISTINCT `jabatan` FROM `rekening_transfers` WHERE `jabatan` IS NOT NULL AND `jabatan` != '' ORDER BY `jabatan` ASC")->fetchAll(PDO::FETCH_COLUMN);

            echo json_encode([
                'status' => 'success',
                'data' => $rows,
                'stats' => $stats,
                'wilayah_list' => $wilayahList,
                'jabatan_list' => $jabatanList
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    // ----------------------------------------------------
    // POST: Ubah status transfer (SUDAH <-> BELUM)
    // ----------------------------------------------------
    case 'toggle_status':
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = intval($input['id'] ?? 0);

            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'ID data tidak valid']);
                exit;
            }

            $currStmt = $pdo->prepare("SELECT `status_tf` FROM `rekening_transfers` WHERE `id` = :id");
            $currStmt->execute([':id' => $id]);
            $currentStatus = strtoupper(trim($currStmt->fetchColumn() ?: 'BELUM'));

            $newStatus = ($currentStatus === 'SUDAH') ? 'BELUM' : 'SUDAH';

            $update = $pdo->prepare("UPDATE `rekening_transfers` SET `status_tf` = :status, `updated_at` = CURRENT_TIMESTAMP WHERE `id` = :id");
            $update->execute([':status' => $newStatus, ':id' => $id]);

            echo json_encode([
                'status' => 'success',
                'id' => $id,
                'new_status' => $newStatus
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    // ----------------------------------------------------
    // POST: Simpan data (Tambah / Edit)
    // ----------------------------------------------------
    case 'save':
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = intval($input['id'] ?? 0);
            $nama = trim($input['nama'] ?? '');
            $jabatan = trim($input['jabatan'] ?? '-');
            if ($jabatan === '') $jabatan = '-';
            $wilayah = trim($input['wilayah'] ?? '-');
            if ($wilayah === '') $wilayah = '-';
            $nominal = floatval($input['nominal'] ?? 0);
            $rekening = preg_replace('/[^0-9]/', '', $input['rekening'] ?? '');
            $statusTf = strtoupper(trim($input['status_tf'] ?? 'BELUM'));

            if ($nama === '' || $rekening === '') {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Nama dan Nomor Rekening wajib diisi!']);
                exit;
            }

            if ($id > 0) {
                // Update
                $sql = "UPDATE `rekening_transfers` SET 
                    `nama` = :nama, 
                    `jabatan` = :jabatan, 
                    `wilayah` = :wilayah, 
                    `nominal` = :nominal, 
                    `rekening` = :rekening, 
                    `status_tf` = :status_tf, 
                    `updated_at` = CURRENT_TIMESTAMP 
                    WHERE `id` = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nama' => $nama,
                    ':jabatan' => $jabatan,
                    ':wilayah' => $wilayah,
                    ':nominal' => $nominal,
                    ':rekening' => $rekening,
                    ':status_tf' => $statusTf,
                    ':id' => $id
                ]);
            } else {
                // Insert Baru
                $sql = "INSERT INTO `rekening_transfers` (`nama`, `jabatan`, `wilayah`, `nominal`, `rekening`, `status_tf`) 
                    VALUES (:nama, :jabatan, :wilayah, :nominal, :rekening, :status_tf)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nama' => $nama,
                    ':jabatan' => $jabatan,
                    ':wilayah' => $wilayah,
                    ':nominal' => $nominal,
                    ':rekening' => $rekening,
                    ':status_tf' => $statusTf
                ]);
                $id = $pdo->lastInsertId();
            }

            echo json_encode(['status' => 'success', 'id' => $id]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    // ----------------------------------------------------
    // POST: Hapus single data
    // ----------------------------------------------------
    case 'delete':
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = intval($input['id'] ?? 0);

            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'ID data tidak valid']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM `rekening_transfers` WHERE `id` = :id");
            $stmt->execute([':id' => $id]);

            echo json_encode(['status' => 'success', 'deleted_id' => $id]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    // ----------------------------------------------------
    // POST: Hapus data berdasarkan jabatan yang dipilih
    // ----------------------------------------------------
    case 'delete_by_jabatan':
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $selectedJabatan = $input['jabatan'] ?? [];
            $deleteAll = !empty($input['delete_all']);

            if ($deleteAll) {
                $pdo->exec("TRUNCATE TABLE `rekening_transfers`");
                echo json_encode([
                    'status' => 'success',
                    'deleted_count' => 'all',
                    'message' => 'Seluruh data rekap transfer berhasil dihapus'
                ]);
                exit;
            }

            if (!is_array($selectedJabatan) || empty($selectedJabatan)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Pilih minimal satu jabatan yang ingin dihapus!']);
                exit;
            }

            $hasDash = in_array('-', $selectedJabatan, true) || in_array('', $selectedJabatan, true);
            $specificJabatan = array_values(array_filter($selectedJabatan, function($j) {
                return $j !== '-' && $j !== '';
            }));

            $whereClauses = [];
            $params = [];

            if (!empty($specificJabatan)) {
                $placeholders = [];
                foreach ($specificJabatan as $idx => $jab) {
                    $ph = ":jab_$idx";
                    $placeholders[] = $ph;
                    $params[$ph] = trim($jab);
                }
                $whereClauses[] = "`jabatan` IN (" . implode(', ', $placeholders) . ")";
            }

            if ($hasDash) {
                $whereClauses[] = "(`jabatan` = '-' OR `jabatan` = '' OR `jabatan` IS NULL)";
            }

            if (empty($whereClauses)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Pilih minimal satu jabatan yang valid!']);
                exit;
            }

            $whereSql = implode(' OR ', $whereClauses);

            // Hitung total baris yang akan dihapus
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM `rekening_transfers` WHERE $whereSql");
            $countStmt->execute($params);
            $deletedCount = (int)$countStmt->fetchColumn();

            // Eksekusi penghapusan
            $delStmt = $pdo->prepare("DELETE FROM `rekening_transfers` WHERE $whereSql");
            $delStmt->execute($params);

            echo json_encode([
                'status' => 'success',
                'deleted_count' => $deletedCount,
                'message' => "Berhasil menghapus $deletedCount data penerima dengan jabatan terpilih"
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    // ----------------------------------------------------
    // POST: Import Massal dari Excel / JSON
    // ----------------------------------------------------
    case 'import':
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $rows = $input['rows'] ?? [];
            $mode = $input['mode'] ?? 'append'; // 'append' atau 'replace'

            if (!is_array($rows) || empty($rows)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Tidak ada baris data yang diimpor']);
                exit;
            }

            $pdo->beginTransaction();

            if ($mode === 'replace') {
                $pdo->exec("TRUNCATE TABLE `rekening_transfers`");
            }

            $insertStmt = $pdo->prepare("INSERT INTO `rekening_transfers` 
                (`nama`, `jabatan`, `wilayah`, `nominal`, `rekening`, `status_tf`) 
                VALUES (:nama, :jabatan, :wilayah, :nominal, :rekening, :status_tf)");

            $insertedCount = 0;
            foreach ($rows as $r) {
                $nama = trim($r['nama'] ?? '');
                $jabatan = trim($r['jabatan'] ?? '-');
                if ($jabatan === '') $jabatan = '-';
                $wilayah = trim($r['wilayah'] ?? '-');
                if ($wilayah === '') $wilayah = '-';
                $nominalRaw = str_replace([',', '.', 'Rp', 'rp', ' '], '', strval($r['nominal'] ?? '0'));
                $nominal = floatval($nominalRaw);
                $rekening = preg_replace('/[^0-9]/', '', strval($r['rekening'] ?? ''));
                $statusTf = strtoupper(trim($r['status_tf'] ?? 'BELUM'));
                if ($statusTf !== 'SUDAH') $statusTf = 'BELUM';

                if ($nama !== '' && $rekening !== '') {
                    $insertStmt->execute([
                        ':nama' => $nama,
                        ':jabatan' => $jabatan,
                        ':wilayah' => $wilayah,
                        ':nominal' => $nominal,
                        ':rekening' => $rekening,
                        ':status_tf' => $statusTf
                    ]);
                    $insertedCount++;
                }
            }

            $pdo->commit();

            echo json_encode([
                'status' => 'success',
                'inserted' => $insertedCount,
                'message' => "Berhasil mengimpor $insertedCount data transfer!"
            ]);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    // ----------------------------------------------------
    // POST: Reset / Kosongkan semua data
    // ----------------------------------------------------
    case 'truncate':
        try {
            $pdo->exec("TRUNCATE TABLE `rekening_transfers`");
            echo json_encode(['status' => 'success', 'message' => 'Seluruh data rekap transfer berhasil dikosongkan']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Action tidak dikenali']);
        break;
}
