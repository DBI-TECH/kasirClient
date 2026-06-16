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
    
    $request_uri = $_SERVER['REQUEST_URI'];
    
    if (strpos($request_uri, '/kasirClient/') !== false) {
        define('BASE_URL', $protocol . $host . '/kasirClient/');
    } else {
        define('BASE_URL', $protocol . $host . '/');
    }
}

// ========== FUNGSI CEK STOK ==========
function cekStok($conn, $id_barang, $jumlah) {
    if ($jumlah <= 0) return false;
    $id_barang = (int)$id_barang;
    $query = "SELECT stok FROM barang WHERE id_barang = $id_barang";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        return $data['stok'] >= $jumlah;
    }
    return false;
}

function kurangiStok($conn, $id_barang, $jumlah) {
    $query = "UPDATE barang SET stok = stok - $jumlah WHERE id_barang = $id_barang";
    return mysqli_query($conn, $query);
}

function getStok($conn, $id_barang) {
    $query = "SELECT stok FROM barang WHERE id_barang = $id_barang";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        return $data['stok'];
    }
    return 0;
}

function getStokMenipis($conn, $limit = 10) {
    $query = "SELECT * FROM barang WHERE stok <= $limit AND stok > 0 ORDER BY stok ASC";
    return mysqli_query($conn, $query);
}

function getStokHabis($conn) {
    $query = "SELECT * FROM barang WHERE stok = 0";
    return mysqli_query($conn, $query);
}
?>