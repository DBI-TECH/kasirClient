<?php
require_once '../../config/database.php';
require_once '../../includes/fungsi.php';
require_once '../../includes/header.php';

$itemsByTipe = ambilBarangGroupedByTipe($conn);

$categoryOrder = ['mocktail', 'milk base', 'coffe', 'snack'];
$orderedItems = [];
foreach ($categoryOrder as $cat) {
    if (isset($itemsByTipe[$cat])) {
        $orderedItems[$cat] = $itemsByTipe[$cat];
    }
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1><i class="fas fa-utensils"></i> Daftar Menu</h1>
    <a href="tambah.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Menu
    </a>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th width="50">No</th>
                <th>Kategori</th>
                <th>Nama Menu</th>
                <th>Harga</th>
                <th width="120">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            foreach ($orderedItems as $kategori => $items):
                foreach ($items as $barang):
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= ucfirst($kategori) ?></td>
                <td><?= htmlspecialchars($barang['nama_barang']) ?></td>
                <td><?= rupiah($barang['harga']) ?></div>
                <td>
                    <a href="edit.php?id=<?= $barang['id_barang'] ?>" class="action-link">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="hapus.php?id=<?= $barang['id_barang'] ?>" class="action-link action-link-danger" onclick="return confirm('Yakin ingin menghapus menu ini?')">
                        <i class="fas fa-trash"></i> Hapus
                    </a>
                 </div>
            </tr>
            <?php 
                endforeach;
            endforeach;
            if ($no == 1): ?>
            <tr>
                <td colspan="5" style="text-align: center; padding: 40px;">Belum ada data menu</div>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>