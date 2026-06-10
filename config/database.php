<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'kasir_db';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Tentukan BASE_URL secara dinamis
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    
    // Deteksi jika subdirectory atau virtual host
    $request_uri = $_SERVER['REQUEST_URI'];
    
    // Jika ada /kasirClient/ di path, gunakan itu sebagai base
    if (strpos($request_uri, '/kasirClient/') !== false) {
        define('BASE_URL', $protocol . $host . '/kasirClient/');
    } else {
        // Jika tidak ada, asumsikan ini adalah root (virtual host)
        define('BASE_URL', $protocol . $host . '/');
    }
}
?>