<?php
require_once '../../config/database.php';
require_once '../../includes/fungsi.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: menu.php');
    exit;
}

// Cek apakah barang memiliki transaksi
$cekTransaksi = mysqli_query($conn, "SELECT COUNT(*) as total FROM detail_transaksi WHERE id_barang = $id");
$data = mysqli_fetch_assoc($cekTransaksi);

if ($data['total'] > 0) {
    // Jika ada transaksi, lakukan soft delete
    mysqli_query($conn, "UPDATE barang SET is_active = 0 WHERE id_barang = $id");
    $_SESSION['success'] = "Menu berhasil dinonaktifkan (soft delete)";
} else {
    // Jika tidak ada transaksi, hapus permanen
    mysqli_query($conn, "DELETE FROM barang WHERE id_barang = $id");
    $_SESSION['success'] = "Menu berhasil dihapus";
}

header('Location: menu.php');
exit;
?>