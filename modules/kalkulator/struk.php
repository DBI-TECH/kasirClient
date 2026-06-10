<?php
require_once '../../config/database.php';
require_once '../../includes/fungsi.php';
require_once '../../includes/header.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id_transaksi = (int)($_GET['id'] ?? 0);

$last = $_SESSION['last_struk'] ?? null;

$kasir = $last['kasir'] ?? 'Adni Budi';
$nama_pemesan = $last['nama_pemesan'] ?? '-';
$total = $last['total'] ?? 0;
$tgl = $last['tgl'] ?? date('Y-m-d H:i:s');

if ((!$last || empty($last)) && $id_transaksi > 0) {
    $qTrans = mysqli_query($conn, "SELECT * FROM transaksi WHERE id_transaksi=$id_transaksi");
    $transaksi = mysqli_fetch_assoc($qTrans);
    if ($transaksi) {
        $nama_pemesan = $transaksi['nama_pemesan'] ?? '-';
        $total = (int)$transaksi['total'];
        $tgl = $transaksi['tgl_transaksi'] ?? $tgl;
        $kasir = 'Adni Budi';
    }
}

$items = [];
if ($id_transaksi > 0) {
    $qDetail = mysqli_query(
        $conn,
        "SELECT b.nama_barang, b.harga, dt.jumlah, (b.harga * dt.jumlah) AS sub_total
         FROM detail_transaksi dt
         JOIN barang b ON b.id_barang = dt.id_barang
         WHERE dt.id_transaksi = $id_transaksi"
    );
    while ($d = mysqli_fetch_assoc($qDetail)) {
        $items[] = $d;
    }
}

?>

<h2 style="margin-bottom: 12px;"><i class="fas fa-receipt"></i> Struk Transaksi</h2>

<div class="table-wrapper" style="max-width: 520px; margin: 0 auto;">
    <div style="padding: 18px;">
        <div style="text-align:center; margin-bottom: 8px;">
            <div style="font-weight:800; font-size: 18px;">Tuklife</div>
            <div style="font-size: 12px; color: #64748b;">Jl. Demo No. 1 (Contoh)</div>
            <div style="font-size: 12px; color: #64748b;">ID Transaksi: <b><?= htmlspecialchars((string)$id_transaksi) ?></b></div>
        </div>
        <hr style="border:none;border-top:1px dashed #e4e9f2; margin: 12px 0;" />

        <div style="font-size: 13px; line-height: 1.7;">
            <div><b>Kasir:</b> <?= htmlspecialchars((string)$kasir) ?></div>
            <div><b>Nama Pemesan:</b> <?= htmlspecialchars((string)$nama_pemesan) ?></div>
            <div><b>Tanggal:</b> <?= htmlspecialchars(date('d/m/Y H:i:s', strtotime((string)$tgl))) ?></div>
        </div>

        <hr style="border:none;border-top:1px dashed #e4e9f2; margin: 12px 0;" />

        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 1px solid #e4e9f2;">
                    <th style="text-align:left; padding: 6px 0;">Menu</th>
                    <th style="text-align:right; padding: 6px 0; width: 55px;">Qty</th>
                    <th style="text-align:right; padding: 6px 0; width: 90px;">Sub</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td style="padding: 6px 0;"><?= htmlspecialchars((string)$it['nama_barang']) ?></td>
                            <td style="padding: 6px 0; text-align:right;"><?= (int)$it['jumlah'] ?></td>
                            <td style="padding: 6px 0; text-align:right;"><?= rupiah((int)$it['sub_total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="padding: 10px 0; text-align:center; color:#64748b;">Item tidak ditemukan</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <hr style="border:none;border-top:1px solid #e4e9f2; margin: 12px 0;" />

        <div style="display:flex; justify-content: space-between; align-items:center; font-size: 14px;">
            <div style="font-weight:700;">Total</div>
            <div style="font-weight:800; color: #ffcc00; font-size: 18px;"><?= rupiah((int)$total) ?></div>
        </div>

        <div style="margin-top: 10px; font-size: 11px; color:#64748b; text-align:center;">
            Terima kasih sudah berbelanja.
        </div>
    </div>
</div>

<div class="action-buttons" style="justify-content:center; margin-top: 16px;">
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali ke Kalkulator
    </a>
    <button onclick="window.print()" class="btn btn-primary">
        <i class="fas fa-print"></i> Cetak Struk
    </button>
</div>

<script>
    window.addEventListener('load', function () {
        setTimeout(function () {
            try { window.print(); } catch (e) {}
        }, 300);
    });
</script>

<?php
unset($_SESSION['last_struk']);
include '../../includes/footer.php';
?>