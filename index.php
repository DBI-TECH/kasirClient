<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'config/database.php';
include 'includes/header.php';
include 'includes/fungsi.php';

$totalBarang = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM barang"));
$totalTransaksi = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM transaksi"));
$totalStok = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM stok"));

// ===== PERBAIKAN: Query yang konsisten =====
$queryHariIni = "SELECT COUNT(*) as total, COALESCE(SUM(total), 0) as pendapatan 
                FROM transaksi 
                WHERE DATE(tgl_transaksi) = CURDATE()";
// ==========================================

$resultHariIni = mysqli_query($conn, $queryHariIni);
$dataHariIni = mysqli_fetch_assoc($resultHariIni);

// Cek stok menipis
$stokMenipis = getStokMenipis($conn, 5);
if ($stokMenipis && mysqli_num_rows($stokMenipis) > 0):
?>
// Cek stok menipis
$stokMenipis = getStokMenipis($conn, 5);
if (mysqli_num_rows($stokMenipis) > 0):
?>
<div class="alert alert-warning" style="margin-bottom:20px;">
    <i class="fas fa-exclamation-triangle"></i>
    <strong>⚠️ Peringatan Stok!</strong> 
    Terdapat <?= mysqli_num_rows($stokMenipis) ?> menu dengan stok menipis (≤ 5).
    <a href="<?= BASE_URL ?>modules/menu/index.php" style="color:#92400e;font-weight:bold;">Kelola Menu →</a>
</div>
<?php endif; ?>

<?php
$stokHabis = getStokHabis($conn);
if (mysqli_num_rows($stokHabis) > 0):
?>
<div class="alert alert-danger" style="margin-bottom:20px;">
    <i class="fas fa-times-circle"></i>
    <strong>❌ Stok Habis!</strong> 
    Terdapat <?= mysqli_num_rows($stokHabis) ?> menu yang stoknya habis.
    <a href="<?= BASE_URL ?>modules/menu/index.php" style="color:#991b1b;font-weight:bold;">Kelola Menu →</a>
</div>
<?php endif; ?>
<h1><i class="fas fa-chalkboard-user"></i> Selamat Datang <?= $_SESSION['nama_kasir']; ?></h1>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= $totalBarang ?></div>
        <div class="stat-label"><i class="fas fa-utensils"></i> Total Menu</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $dataHariIni['total'] ?? 0 ?></div>
        <div class="stat-label"><i class="fas fa-calendar-day"></i> Transaksi Hari Ini</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $totalStok ?></div>
        <div class="stat-label"><i class="fas fa-boxes"></i> Jenis Bahan Stok</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $dataHariIni['total'] ?></div>
        <div class="stat-label"><i class="fas fa-calendar-day"></i> Transaksi Hari Ini</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= rupiah($dataHariIni['pendapatan']) ?></div>
        <div class="stat-label"><i class="fas fa-money-bill-wave"></i> Pendapatan Hari Ini</div>
    </div>
</div>

<div class="action-buttons">
    <a href="<?= BASE_URL ?>modules/kalkulator/index.php" class="btn btn-primary">
        <i class="fas fa-calculator"></i> Mulai Transaksi Baru
    </a>
    <a href="<?= BASE_URL ?>modules/menu/index.php" class="btn btn-success">
        <i class="fas fa-utensils"></i> Kelola Menu
    </a>
    <a href="<?= BASE_URL ?>modules/stok-bahan/index.php" class="btn btn-secondary">
        <i class="fas fa-boxes"></i> Kelola Stok
    </a>
</div>

<?php include 'includes/footer.php'; ?>