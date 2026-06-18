<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/fungsi.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

// Restore menu (aktifkan kembali)
$result = restoreMenu($conn, $id);

if ($result) {
    $_SESSION['success'] = "✅ Menu berhasil diaktifkan kembali!";
} else {
    $_SESSION['error'] = "❌ Gagal mengaktifkan menu: " . mysqli_error($conn);
}

header('Location: index.php');
exit;
?>