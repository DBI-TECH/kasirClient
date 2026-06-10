<?php
require_once '../../config/database.php';
require_once '../../includes/fungsi.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

mysqli_query($conn, "DELETE FROM barang WHERE id_barang=$id");
header('Location: index.php');
exit;