<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/fungsi.php';

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
    $stok = (int)$_POST['stok'];
    
    $query = "UPDATE barang SET 
              nama_barang='$nama', 
              harga=$harga, 
              stok=$stok 
              WHERE id_barang=$id";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "✅ Menu berhasil diupdate!";
    } else {
        $_SESSION['error'] = "❌ Gagal mengupdate menu: " . mysqli_error($conn);
    }
    header('Location: index.php');
    exit;
}

include '../../includes/header.php';
?>

<h2>Edit Menu</h2>
<a href="index.php" class="back-btn" style="display:inline-block;margin-bottom:16px;">← Kembali</a>

<form method="POST">
    <div class="form-group">
        <label>Nama Menu</label>
        <input type="text" name="nama_menu" value="<?= htmlspecialchars($item['nama_barang'] ?? '') ?>" required>
    </div>
    <div class="form-group">
        <label>Harga</label>
        <input type="number" name="harga" value="<?= htmlspecialchars($item['harga'] ?? 0) ?>" required>
    </div>
    <div class="form-group">
        <label>Stok</label>
        <input type="number" name="stok" value="<?= htmlspecialchars($item['stok'] ?? 0) ?>" min="0" required>
    </div>
    <button type="submit">Simpan Perubahan</button>
</form>

<?php include '../../includes/footer.php'; ?>