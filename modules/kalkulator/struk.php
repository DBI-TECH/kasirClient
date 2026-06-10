<?php
// Session harus dipanggil PALING ATAS, sebelum APAPUN output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/database.php';
require_once '../../includes/fungsi.php';

// Ambil ID transaksi dari URL
$id_transaksi = (int)($_GET['id'] ?? 0);

// Jika tidak ada ID, redirect ke halaman kalkulator
if ($id_transaksi <= 0) {
    header('Location: index.php');
    exit;
}

// Ambil data transaksi
$qTrans = mysqli_query($conn, "SELECT * FROM transaksi WHERE id_transaksi = $id_transaksi");
$transaksi = mysqli_fetch_assoc($qTrans);

// Jika transaksi tidak ditemukan, redirect
if (!$transaksi) {
    header('Location: index.php');
    exit;
}

// Ambil nama kasir dari session
$nama_kasir = $_SESSION['nama_kasir'] ?? 'Kasir tidak diketahui';

// Ambil detail transaksi
$qDetail = mysqli_query($conn, 
    "SELECT dt.id_detail, b.nama_barang, b.harga, dt.jumlah, (b.harga * dt.jumlah) AS sub_total
     FROM detail_transaksi dt
     JOIN barang b ON b.id_barang = dt.id_barang
     WHERE dt.id_transaksi = $id_transaksi"
);

// Setelah semua proses selesai, baru include header
require_once '../../includes/header.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Struk Transaksi</title>
    <style>

.struk-container{
    width:80mm;
    margin:20px auto;
    background:#fff;
    color:#000;
    padding:10px;
    font-family:"Courier New", monospace;
    font-size:12px;
    line-height:1.4;
}

.struk-header{
    text-align:center;
}

.struk-header h3{
    margin:0;
    font-size:20px;
    font-weight:bold;
    color:#000;
}

.struk-header p{
    margin:2px 0;
}

.struk-items{
    margin-top:10px;
}

.item-detail{
    margin-bottom:8px;
}

.nama-item{
    font-weight:bold;
}

.struk-item{
    display:flex;
    justify-content:space-between;
}

.struk-total{
    margin-top:10px;
    border-top:1px dashed #000;
    padding-top:8px;
}

.struk-total .struk-item{
    margin-bottom:4px;
}

.struk-footer{
    text-align:center;
    margin-top:15px;
    font-size:11px;
}

.struk-footer p{
    margin:2px 0;
}

.no-print{
    text-align:center;
}

@media print{

    body{
        margin:0;
        padding:0;
        background:#fff;
    }

    h2,
    nav,
    footer,
    .action-buttons,
    .no-print{
        display:none !important;
    }

    .struk-container{
        width:80mm;
        margin:0 auto;
        padding:5px;
        box-shadow:none !important;
        border-radius:0 !important;
    }

    @page{
        size:80mm auto;
        margin:0;
    }
}

</style>
</head>
<body>

<div class="struk-container">

    <div class="struk-header">
        <h3>TUKLIFE</h3>
        <p>================================</p>
        <p>Jl. Depok, Semarang</p>
        <p>Telp. 08xxxxxxxxxx</p>
        <p>================================</p>

        <p>No : TRX-<?= str_pad($transaksi['id_transaksi'], 5, '0', STR_PAD_LEFT) ?></p>
        <p><?= date('d/m/Y H:i:s', strtotime($transaksi['tgl_transaksi'])) ?></p>
        <p>Kasir : <?= htmlspecialchars($nama_kasir) ?></p>
        <p>Pemesan : <?= htmlspecialchars($transaksi['nama_pemesan'] ?? '-') ?></p>

        <p>--------------------------------</p>
    </div>

    <div class="struk-items">

        <?php while($d = mysqli_fetch_assoc($qDetail)): ?>

            <div class="item-detail">

                <div class="nama-item">
                    <?= htmlspecialchars($d['nama_barang']) ?>
                </div>

                <div class="struk-item">
                    <span>
                        <?= $d['jumlah'] ?> x <?= number_format($d['harga'],0,',','.') ?>
                    </span>

                    <span>
                        <?= number_format($d['sub_total'],0,',','.') ?>
                    </span>
                </div>

            </div>

        <?php endwhile; ?>

    </div>

    <div class="struk-total">

       <div class="struk-item">
    <span>Subtotal</span>
    <span><?= rupiah($transaksi['total']) ?></span>
</div>

<div class="struk-item">
    <span>Cash</span>
    <span><?= rupiah($transaksi['cash']) ?></span>
</div>

<div class="struk-item">
    <span>Change</span>
    <span><?= rupiah($transaksi['change']) ?></span>
</div>

<div class="struk-item">
    <span>----------------</span>
    <span>----------------</span>
</div>

<div class="struk-item">
    <strong>TOTAL</strong>
    <strong><?= rupiah($transaksi['total']) ?></strong>
</div>

    </div>

    <div class="struk-footer">
        <p>================================</p>
        <p>TERIMA KASIH</p>
        <p>Atas Kunjungan Anda</p>
        <p>Simpan Struk Sebagai Bukti</p>
        <p>================================</p>
    </div>

</div>

<div class="no-print" style="text-align: center; margin-top: 20px;">
    <button onclick="window.print()" class="btn btn-primary">
        <i class="fas fa-print"></i> Cetak Struk
    </button>
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<?php include '../../includes/footer.php'; ?>
</body>
</html>