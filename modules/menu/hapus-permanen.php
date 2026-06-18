<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/fungsi.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

// Cek apakah barang memiliki transaksi (untuk keamanan)
$cekTransaksi = mysqli_query($conn, "SELECT COUNT(*) as total FROM detail_transaksi WHERE id_barang = $id");
$data = mysqli_fetch_assoc($cekTransaksi);
$totalTransaksi = $data['total'] ?? 0;

if ($totalTransaksi > 0) {
    $_SESSION['error'] = "❌ Menu tidak bisa dihapus permanen karena sudah memiliki $totalTransaksi transaksi! Gunakan soft delete saja.";
    header('Location: index.php');
    exit;
}

// Ambil nama barang untuk pesan
$namaQuery = mysqli_query($conn, "SELECT nama_barang FROM barang WHERE id_barang = $id");
$namaData = mysqli_fetch_assoc($namaQuery);
$namaBarang = $namaData['nama_barang'] ?? 'Menu';

// Hapus permanen
$query = "DELETE FROM barang WHERE id_barang = $id";
$result = mysqli_query($conn, $query);

if ($result) {
    $_SESSION['success'] = "✅ Menu '{$namaBarang}' berhasil dihapus permanen!";
} else {
    $_SESSION['error'] = "❌ Gagal menghapus permanen menu: " . mysqli_error($conn);
}

header('Location: index.php');
exit;
?>