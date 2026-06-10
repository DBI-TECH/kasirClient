<?php
include '../../config/database.php';
include '../../includes/fungsi.php';
include '../../includes/header.php';

$availableTypes = ['mocktail', 'milk base', 'coffe', 'snack', 'lainnya'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_menu']);
    $harga = (int)$_POST['harga'];
    $tipe = 'Umum';
    if (barang_has_tipe_column($conn)) {
        $tipeInput = trim($_POST['tipe'] ?? 'lainnya');
        $tipe = in_array($tipeInput, $availableTypes, true) ? $tipeInput : 'lainnya';
        $query = "INSERT INTO barang (nama_barang, harga, tipe) VALUES ('$nama', $harga, '$tipe')";
    } else {
        $query = "INSERT INTO barang (nama_barang, harga) VALUES ('$nama', $harga)";
    }
    mysqli_query($conn, $query);
    header("Location: index.php");
    exit;
}
?>
<div class="page-header">
    <h2>Tambah Menu</h2>
    <a href="index.php" class="back-btn">← Kembali ke Menu</a>
</div>
<form method="POST">
    <div class="form-group">
        <label>Nama Menu</label>
        <input type="text" name="nama_menu" required>
    </div>
    <div class="form-group">
        <label>Harga</label>
        <input type="number" name="harga" required>
    </div>
    <?php if (barang_has_tipe_column($conn)): ?>
    <div class="form-group">
        <label>Kategori</label>
        <select name="tipe" required>
            <?php foreach ($availableTypes as $type): ?>
                <option value="<?= htmlspecialchars($type) ?>">
                    <?= htmlspecialchars(ucwords($type)) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
    <button type="submit">Simpan</button>
</form>
<?php include '../../includes/footer.php'; ?>