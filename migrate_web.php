<?php
// ============================================================
// Portofolio — Web Migration Trigger & Status Console
// ============================================================
$valid_token = 'corefive123';
$request_token = $_GET['token'] ?? '';

if ($request_token !== $valid_token) {
    http_response_code(403);
    die("Akses Ditolak: Token tidak valid. Gunakan parameter ?token=$valid_token");
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Portofolio Corefive - Database Migration Console</title>
    <style>
        body { background: #121212; color: #00ff66; font-family: monospace; padding: 25px; line-height: 1.6; }
        pre { background: #1e1e1e; padding: 15px; border-radius: 8px; border: 1px solid #333; color: #fff; overflow-x: auto; font-size: 14px; }
        .btn { display: inline-block; padding: 10px 18px; background: #00ff66; color: #000; text-decoration: none; font-weight: bold; border-radius: 6px; margin-right: 10px; transition: 0.2s; }
        .btn:hover { background: #00cc52; }
        .btn-warning { background: #ffbb00; color: #000; }
        .btn-warning:hover { background: #d49b00; }
        .info { color: #888; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h2>🚀 Portofolio Corefive — Database Migration Console</h2>
    <p class="info">Konsol visual untuk memantau dan memicu migrasi skema database di server.</p>
    <div style="margin-bottom: 20px;">
        <a class="btn" href="?token=<?= htmlspecialchars($valid_token) ?>&action=migrate">⚡ Jalankan Migrasi (Migrate)</a>
        <a class="btn" href="?token=<?= htmlspecialchars($valid_token) ?>&action=status">📊 Cek Status Migrasi</a>
    </div>
    <pre><?php
    // Siapkan kredensial trigger untuk migrate.php
    $_GET['key'] = 'corefive2026';
    $act = $_GET['action'] ?? 'status';
    $_GET['action'] = ($act === 'migrate') ? 'up' : $act;
    require_once __DIR__ . '/migrate.php';
    ?></pre>
</body>
</html>

