<?php
include '../../config/database.php';
include '../../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_stok']);
    $stok = (int)$_POST['stok'];
    $query = "INSERT INTO stok (nama_stok, stok) VALUES ('$nama', $stok)";
    mysqli_query($conn, $query);
    header("Location: index.php");
    exit;
}
?>

<div class="page-header">
    <h2 class="page-title">Tambah Stok Bahan</h2>
    <a href="index.php" class="back-btn">← Kembali ke Data Stok</a>
</div>

<form method="POST" class="stok-form">
    <div class="form-group">
        <label>Nama Stok</label>
        <input type="text" name="nama_stok" required>
    </div>
    <div class="form-group">
        <label>Jumlah Stok</label>
        <input type="number" name="stok" required>
    </div>
    <button type="submit" class="submit-btn">Simpan</button>
</form>
<?php include '../../includes/footer.php'; ?>