<?php
// koneksi.php
$host = 'localhost';
$dbname = 'portfolio_db';
$username = 'root'; // Sesuaikan jika menggunakan username yang berbeda di XAMPP
$password = ''; // Sesuaikan jika ada password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Mengatur PDO error mode menjadi exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Menjadikan hasil query menjadi associative array secara default
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>
