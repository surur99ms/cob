<?php
/**
 * JEMBATAN SINKRONISASI PRESENSI HALACODING
 * Bertugas menyinkronkan data kehadiran saat peserta absen di HP agar langsung tampil di Laptop Admin.
 * 
 * CATATAN PENTING:
 * - File ini TIDAK MEMERLUKAN DATABASE MySQL sama sekali.
 * - Data master peserta TETAP berada di dalam file kode (data-peserta.js).
 * - File ini hanya menerima catatan tanda hadir (nama, jam hadir, rating) dan menyimpannya ke file rekap_presensi.json.
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

// Inisialisasi file rekap jika belum ada
if (!file_exists($rekapFile)) {
    file_put_contents($rekapFile, '[]', LOCK_EX);
}

// Helper membaca data rekap
function getRekapData($filePath) {
    if (!file_exists($filePath)) return [];
    $raw = file_get_contents($filePath);
    if ($raw === false || trim($raw) === '') return [];
    $json = json_decode($raw, true);
    return is_array($json) ? $json : [];
}

// Helper menyimpan data rekap dengan atomic lock
function saveRekapData($filePath, $data) {
    $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($filePath, $encoded, LOCK_EX) !== false;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_rekap':
        $data = getRekapData($rekapFile);
        echo json_encode([
            'status' => 'success',
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

        $rekap = getRekapData($rekapFile);
        $existingIndex = -1;

        // Cari jika peserta ini sudah pernah absen (update data)
        foreach ($rekap as $idx => $r) {
            if (strcasecmp($r['idPeserta'], $record['idPeserta']) === 0) {
                $existingIndex = $idx;
                break;
            }
        }

        if ($existingIndex !== -1) {
            $rekap[$existingIndex] = $record;
        } else {
            array_unshift($rekap, $record);
        }

        if (saveRekapData($rekapFile, $rekap)) {
            echo json_encode([
                'status' => 'success',
                'record' => $record,
                'total' => count($rekap)
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan presensi di server']);
        }
        break;

    case 'delete_absen':
        $id = $_GET['id'] ?? '';
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']);
            exit;
        }

        $rekap = getRekapData($rekapFile);
        $filtered = array_values(array_filter($rekap, function($r) use ($id) {
            return strcasecmp($r['idPeserta'], $id) !== 0;
        }));

        saveRekapData($rekapFile, $filtered);
        echo json_encode(['status' => 'success', 'total' => count($filtered)]);
        break;

    case 'reset_rekap':
        saveRekapData($rekapFile, []);
        echo json_encode(['status' => 'success', 'message' => 'Rekap presensi berhasil direset']);
        break;

    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Action tidak dikenali']);
        break;
}
