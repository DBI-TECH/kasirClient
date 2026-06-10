<?php
require_once '../../config/database.php';
require_once '../../includes/fungsi.php';
require_once '../../includes/header.php';

$query = "SELECT * FROM stok ORDER BY id_stok ASC";
$result = mysqli_query($conn, $query);

$totalJenisStok = $result ? mysqli_num_rows($result) : 0;
$totalStokUnit = 0;
$totalLowStok = 0;

$sumResult = mysqli_query($conn, "SELECT COALESCE(SUM(stok),0) as total_unit FROM stok");
$totalStokUnit = (int)($sumResult ? mysqli_fetch_assoc($sumResult)['total_unit'] : 0);
$lowStokResult = mysqli_query($conn, "SELECT COUNT(*) as total_low FROM stok WHERE stok <= 10");
$totalLowStok = (int)($lowStokResult ? mysqli_fetch_assoc($lowStokResult)['total_low'] : 0);
?>

<h1><i class="fas fa-boxes"></i> Data Stok Bahan</h1>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= $totalJenisStok ?></div>
        <div class="stat-label"><i class="fas fa-layer-group"></i> Jenis Bahan</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $totalStokUnit ?></div>
        <div class="stat-label"><i class="fas fa-boxes"></i> Total Unit Stok</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $totalLowStok ?></div>
        <div class="stat-label"><i class="fas fa-exclamation-triangle"></i> Stok Rendah</div>
    </div>
</div>

<div class="action-buttons" style="margin-bottom: 20px;">
    <a href="tambah.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Stok
    </a>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th width="50">No</th>
                <th>Nama Barang</th>
                <th>Stok</th>
                <th width="120">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if(mysqli_num_rows($result) > 0):
                while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['nama_stok'] ?? '') ?></td>
                    <td>
                        <span class="badge badge-<?= ($row['stok'] <= 10) ? 'danger' : 'success' ?>">
                            <?= $row['stok'] ?? 0 ?> unit
                        </span>
                    </td>
                    <td>
                        <a href="edit.php?id=<?= $row['id_stok'] ?>" class="action-link">
                            <i class="fas fa-edit"></i> EDIT
                        </a>
                        <a href="hapus.php?id=<?= $row['id_stok'] ?>" class="action-link action-link-danger" onclick="return confirm('Yakin ingin menghapus stok ini?')">
                            <i class="fas fa-trash"></i> HAPUS
                        </a>
                    </td>
                </tr>
                <?php endwhile; 
            else: ?>
                <tr>
                    <td colspan="4" style="text-align: center;">Belum ada data stok</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>