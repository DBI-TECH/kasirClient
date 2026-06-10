<?php
require_once '../../config/database.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

mysqli_query($conn, "DELETE FROM stok WHERE id_stok=$id");
header('Location: index.php');
exit;