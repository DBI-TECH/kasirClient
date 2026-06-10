<?php
require_once '../../config/database.php';
require_once '../../includes/fungsi.php';
require_once '../../includes/header.php';

$allMenu = ambilSemuaBarang($conn);
$itemsByTipe = ambilBarangGroupedByTipe($conn);

$categoryOrder = ['mocktail', 'milk base', 'coffe', 'snack'];
$orderedItems = [];
foreach ($categoryOrder as $cat) {
    if (isset($itemsByTipe[$cat])) {
        $orderedItems[$cat] = $itemsByTipe[$cat];
    }
}

$totalMenu = count($allMenu);
$totalKategori = count($orderedItems);
$totalHarga = 0;
foreach ($allMenu as $barang) {
    $totalHarga += (int)$barang['harga'];
}
?>

<div class="flex-between" style="margin-bottom: 24px;">
    <div>
        <h1><i class="fas fa-utensils"></i> Daftar Menu</h1>
    </div>
    <a href="tambah.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Menu
    </a>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= $totalMenu ?></div>
        <div class="stat-label"><i class="fas fa-list"></i> Total Menu</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $totalKategori ?></div>
        <div class="stat-label"><i class="fas fa-tags"></i> Kategori Menu</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= rupiah($totalHarga) ?></div>
        <div class="stat-label"><i class="fas fa-wallet"></i> Total Harga Menu</div>
    </div>
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
                <td><?= rupiah($barang['harga']) ?></td>
                <td>
                    <a href="edit.php?id=<?= $barang['id_barang'] ?>" class="action-link">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="hapus.php?id=<?= $barang['id_barang'] ?>" class="action-link action-link-danger" onclick="return confirm('Yakin ingin menghapus menu ini?')">
                        <i class="fas fa-trash"></i> Hapus
                    </a>
                </td>
            </tr>
            <?php 
                endforeach;
            endforeach;
            if ($no == 1): ?>
            <tr>
                <td colspan="5" style="text-align: center; padding: 40px;">Belum ada data menu</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>