<?php
include '../../config/database.php';
include '../../includes/fungsi.php';
include '../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM barang WHERE id_barang=$id");
$item = mysqli_fetch_assoc($result);
if (!$item) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_menu']);
    $harga = (int)$_POST['harga'];
    $query = "UPDATE barang SET nama_barang='$nama', harga=$harga WHERE id_barang=$id";
    mysqli_query($conn, $query);
    header('Location: index.php');
    exit;
}
?>
<h2>Edit Menu</h2>
<form method="POST">
    <div class="form-group">
        <label>Nama Menu</label>
        <input type="text" name="nama_menu" value="<?= htmlspecialchars($item['nama_barang'] ?? '') ?>" required>
    </div>
    <div class="form-group">
        <label>Harga</label>
        <input type="number" name="harga" value="<?= htmlspecialchars($item['harga'] ?? 0) ?>" required>
    </div>
    <button type="submit">Simpan</button>
</form>
<?php include '../../includes/footer.php'; ?>