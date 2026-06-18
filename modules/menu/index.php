<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/fungsi.php';
require_once '../../includes/header.php';

// Tampilkan pesan sukses/error
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success" style="margin-bottom:20px;padding:14px 18px;border-radius:12px;background:#d1fae5;color:#065f46;border-left:4px solid #10b981;display:flex;align-items:center;gap:10px;">
            <i class="fas fa-check-circle" style="font-size:18px;"></i> 
            <span>' . htmlspecialchars($_SESSION['success']) . '</span>
          </div>';
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger" style="margin-bottom:20px;padding:14px 18px;border-radius:12px;background:#fee2e2;color:#991b1b;border-left:4px solid #ef4444;display:flex;align-items:center;gap:10px;">
            <i class="fas fa-exclamation-circle" style="font-size:18px;"></i> 
            <span>' . htmlspecialchars($_SESSION['error']) . '</span>
          </div>';
    unset($_SESSION['error']);
}

// Ambil data menu (termasuk yang tidak aktif untuk ditampilkan di admin)
$allMenu = ambilSemuaBarangWithInactive($conn);
$itemsByTipe = [];

// Group by tipe
foreach ($allMenu as $item) {
    $tipe = $item['tipe'] ?? 'Umum';
    $itemsByTipe[$tipe][] = $item;
}

// Urutkan kategori
$categoryOrder = ['mocktail', 'milk base', 'coffe', 'snack', 'Umum', 'lainnya'];
$orderedItems = [];
foreach ($categoryOrder as $cat) {
    if (isset($itemsByTipe[$cat])) {
        $orderedItems[$cat] = $itemsByTipe[$cat];
    }
}
// Tambahkan sisa kategori yang tidak terdaftar
foreach ($itemsByTipe as $cat => $items) {
    if (!in_array($cat, $categoryOrder)) {
        $orderedItems[$cat] = $items;
    }
}

$totalMenu = count($allMenu);
$totalAktif = 0;
$totalNonaktif = 0;
foreach ($allMenu as $barang) {
    if (isset($barang['is_active']) && $barang['is_active'] == 0) {
        $totalNonaktif++;
    } else {
        $totalAktif++;
    }
}
$totalKategori = count($orderedItems);
$totalHarga = 0;
foreach ($allMenu as $barang) {
    $totalHarga += (int)$barang['harga'];
}
?>

<div class="flex-between" style="margin-bottom: 24px;">
    <div>
        <h1><i class="fas fa-utensils"></i> Daftar Menu</h1>
        <p style="color: #64748b; font-size: 14px; margin-top: 4px;">
            <span style="color: #10b981;">● Aktif: <?= $totalAktif ?></span>
            <span style="color: #ef4444; margin-left: 12px;">● Nonaktif: <?= $totalNonaktif ?></span>
        </p>
    </div>
    <a href="tambah.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Menu
    </a>
</div>

<div class="stats-grid" style="margin-bottom: 24px;">
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
                <th>Stok</th>
                <th width="100">Status</th>
                <th width="160">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if (count($allMenu) > 0):
                foreach ($orderedItems as $kategori => $items):
                    foreach ($items as $barang):
                        $isActive = !isset($barang['is_active']) || $barang['is_active'] == 1;
            ?>
            <tr style="<?= !$isActive ? 'background-color: #fef2f2; opacity: 0.7;' : '' ?>">
                <td><?= $no++ ?></td>
                <td><?= ucfirst($kategori) ?></td>
                <td><?= htmlspecialchars($barang['nama_barang']) ?></td>
                <td><?= rupiah($barang['harga']) ?></td>
                <td><?= $barang['stok'] ?? 0 ?></td>
                <td>
                    <?php if ($isActive): ?>
                        <span class="badge badge-success" style="background:#d1fae5;color:#065f46;padding:4px 12px;border-radius:20px;font-size:12px;">
                            <i class="fas fa-check-circle"></i> Aktif
                        </span>
                    <?php else: ?>
                        <span class="badge badge-danger" style="background:#fee2e2;color:#991b1b;padding:4px 12px;border-radius:20px;font-size:12px;">
                            <i class="fas fa-times-circle"></i> Nonaktif
                        </span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($isActive): ?>
                        <a href="edit.php?id=<?= $barang['id_barang'] ?>" class="action-link">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="hapus.php?id=<?= $barang['id_barang'] ?>" 
                           class="action-link action-link-danger" 
                           onclick="return confirm('⚠️ Yakin ingin menghapus menu ini?\n\nJika menu sudah pernah ditransaksikan, data akan dinonaktifkan (soft delete).\nJika belum pernah ditransaksikan, akan dihapus permanen.')">
                            <i class="fas fa-trash"></i> Hapus
                        </a>
                    <?php else: ?>
                        <a href="restore.php?id=<?= $barang['id_barang'] ?>" 
                           class="action-link" 
                           style="color:#10b981;"
                           onclick="return confirm('Yakin ingin mengaktifkan kembali menu ini?')">
                            <i class="fas fa-undo"></i> Aktifkan
                        </a>
                        <a href="hapus-permanen.php?id=<?= $barang['id_barang'] ?>" 
                           class="action-link action-link-danger" 
                           onclick="return confirm('⚠️ Yakin ingin menghapus permanen menu ini?\n\nTindakan ini tidak bisa dibatalkan!')">
                            <i class="fas fa-trash"></i> Hapus Permanen
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php 
                    endforeach;
                endforeach;
            else: ?>
            <tr>
                <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">
                    <i class="fas fa-info-circle"></i> Belum ada data menu
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div style="margin-top: 20px; padding: 16px; background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">
    <p style="font-size: 13px; color: #64748b;">
        <i class="fas fa-info-circle"></i> 
        <strong>Keterangan:</strong> 
        Menu dengan status <span style="color:#ef4444;">Nonaktif</span> sudah memiliki transaksi dan tidak bisa dihapus permanen. 
        Menu ini bisa diaktifkan kembali jika diperlukan.
    </p>
</div>

<?php include '../../includes/footer.php'; ?>