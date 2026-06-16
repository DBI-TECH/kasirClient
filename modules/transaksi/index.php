<?php
include '../../config/database.php';
include '../../includes/fungsi.php';
include '../../includes/header.php';

// ==== HITUNG TOTAL TRANSAKSI ====
$queryTotal = "SELECT COUNT(*) as total FROM transaksi";
$resultTotal = mysqli_query($conn, $queryTotal);
if ($resultTotal) {
    $totalTransaksi = mysqli_fetch_assoc($resultTotal)['total'];
} else {
    $totalTransaksi = 0;
}

// Ambil semua transaksi
$query = "SELECT * FROM transaksi ORDER BY id_transaksi DESC";
$result = mysqli_query($conn, $query);

// Statistik hari ini
$queryHariIni = "SELECT COUNT(*) as total, COALESCE(SUM(total), 0) as pendapatan 
                FROM transaksi 
                WHERE DATE(tgl_transaksi) = CURDATE()";
$resultHariIni = mysqli_query($conn, $queryHariIni);
$dataHariIni = mysqli_fetch_assoc($resultHariIni);
?>

<h1><i class="fas fa-receipt"></i> Daftar Transaksi</h1>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= $totalTransaksi ?></div>
        <div class="stat-label"><i class="fas fa-chart-line"></i> Total Transaksi</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $dataHariIni['total'] ?? 0 ?></div>
        <div class="stat-label"><i class="fas fa-calendar-day"></i> Transaksi Hari Ini</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= rupiah($dataHariIni['pendapatan'] ?? 0) ?></div>
        <div class="stat-label"><i class="fas fa-money-bill-wave"></i> Pendapatan Hari Ini</div>
    </div>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th width="50">No</th>
                <th>Tanggal</th>
                <th>Nama Pemesan</th>
                <th>Total</th>
                <th width="100">Detail</th>
                <th width="80">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if(mysqli_num_rows($result) > 0):
                while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($row['tgl_transaksi'])) ?></td>
                    <td><?= htmlspecialchars($row['nama_pemesan'] ?? '-') ?></td>
                    <td><strong><?= rupiah($row['total']) ?></strong></td>
                    <td>
                        <a href="detail.php?id=<?= $row['id_transaksi'] ?>" class="action-link">
                            <i class="fas fa-eye"></i> Lihat
                        </a>
                    </td>
                    <td>
                        <a href="hapus.php?id=<?= $row['id_transaksi'] ?>" class="action-link action-link-danger" onclick="return confirm('Yakin hapus transaksi ini?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; 
            else: ?>
                <tr>
                    <td colspan="6" style="text-align: center;">Belum ada transaksi</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>