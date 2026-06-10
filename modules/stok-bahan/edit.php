<?php
include '../../config/database.php';
include '../../includes/fungsi.php';
include '../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM stok WHERE id_stok=$id");
$item = mysqli_fetch_assoc($result);
if (!$item) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_stok']);
    $stok = (int)$_POST['stok'];
    $query = "UPDATE stok SET nama_stok='$nama', stok=$stok WHERE id_stok=$id";
    mysqli_query($conn, $query);
    header('Location: index.php');
    exit;
}
?>
<h2>Edit Stok Bahan</h2>
<form method="POST">
    <div class="form-group">
        <label>Nama Stok</label>
        <input type="text" name="nama_stok" value="<?= htmlspecialchars($item['nama_stok']) ?>" required>
    </div>
    <div class="form-group">
        <label>Stok</label>
        <input type="number" name="stok" value="<?= htmlspecialchars($item['stok']) ?>" required>
    </div>
    <button type="submit">Simpan</button>
</form>
<?php include '../../includes/footer.php'; ?>