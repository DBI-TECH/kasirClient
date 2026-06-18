<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/fungsi.php';

$availableTypes = ['mocktail', 'milk base', 'coffe', 'snack', 'lainnya'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_menu']);
    $harga = (int)$_POST['harga'];
    $stok = (int)($_POST['stok'] ?? 0); // Tambahkan stok
    
    $tipe = 'Umum';
    if (barang_has_tipe_column($conn)) {
        $tipeInput = trim($_POST['tipe'] ?? 'lainnya');
        $tipe = in_array($tipeInput, $availableTypes, true) ? $tipeInput : 'lainnya';
        $query = "INSERT INTO barang (nama_barang, harga, tipe, stok, is_active) 
                  VALUES ('$nama', $harga, '$tipe', $stok, 1)";
    } else {
        $query = "INSERT INTO barang (nama_barang, harga, stok, is_active) 
                  VALUES ('$nama', $harga, $stok, 1)";
    }
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "✅ Menu berhasil ditambahkan!";
    } else {
        $_SESSION['error'] = "❌ Gagal menambahkan menu: " . mysqli_error($conn);
    }
    
    header("Location: index.php");
    exit;
}

include '../../includes/header.php';
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
    <div class="form-group">
        <label>Stok Awal</label>
        <input type="number" name="stok" value="0" min="0">
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