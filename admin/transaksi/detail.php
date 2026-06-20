<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../../config/database.php';
require_once '../../includes/fungsi.php';

$id_transaksi = (int)($_GET['id'] ?? 0);

// ✅ Ambil data transaksi dengan JOIN ke tabel kasir
$qTrans = mysqli_query($conn, "SELECT t.*, k.nama_kasir 
                               FROM transaksi t
                               LEFT JOIN kasir k ON k.id_kasir = t.id_kasir
                               WHERE t.id_transaksi = $id_transaksi");
$transaksi = mysqli_fetch_assoc($qTrans);

// ✅ Ambil nama kasir dari hasil JOIN
$nama_kasir = $transaksi['nama_kasir'] ?? 'Kasir tidak diketahui';

$qDetail = mysqli_query(
    $conn,
    "SELECT dt.id_detail, b.nama_barang, b.harga, dt.jumlah, (b.harga * dt.jumlah) AS sub_total
     FROM detail_transaksi dt
     JOIN barang b ON b.id_barang = dt.id_barang
     WHERE dt.id_transaksi = $id_transaksi"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes" />
    <title>Detail Transaksi - Admin Tuklife</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>assets/img/logo-sidebar.png">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="../../assets/css/admin-style.css" />
    <style>
        /* ====== DETAIL TRANSAKSI STYLE ====== */
        .detail-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-top: 20px;
        }

        .struk-wrapper {
            background: var(--card);
            border-radius: var(--radius);
            padding: 20px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .struk-wrapper .struk-label {
            width: 100%;
            text-align: center;
            margin-bottom: 15px;
            color: var(--text-secondary);
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 1px;
        }

        .struk-container {
            width: 80mm;
            background: #fff;
            color: #000;
            padding: 10px;
            font-family: "Courier New", monospace;
            font-size: 12px;
            line-height: 1.4;
            border: 1px solid var(--border);
            border-radius: var(--radius-xs);
            box-shadow: var(--shadow);
        }

        .struk-header {
            text-align: center;
        }

        .struk-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
            color: #000;
        }

        .struk-header p {
            margin: 2px 0;
        }

        .struk-items {
            margin-top: 10px;
        }

        .item-detail {
            margin-bottom: 8px;
        }

        .nama-item {
            font-weight: bold;
        }

        .struk-item {
            display: flex;
            justify-content: space-between;
        }

        .struk-total {
            margin-top: 10px;
            border-top: 1px dashed #000;
            padding-top: 8px;
        }

        .struk-total .struk-item {
            margin-bottom: 4px;
        }

        .struk-footer {
            text-align: center;
            margin-top: 15px;
            font-size: 11px;
        }

        .struk-footer p {
            margin: 2px 0;
        }

        /* ====== INFO CARD ====== */
        .info-card {
            background: var(--card);
            border-radius: var(--radius);
            padding: 20px 24px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }

        .info-card .card-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-card .card-title i {
            color: var(--primary);
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 30px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .info-item .label {
            font-size: 11px;
            color: var(--text-secondary);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-item .value {
            font-size: 15px;
            font-weight: 600;
            color: var(--text);
        }

        .info-item .value.total {
            color: var(--primary-dark);
            font-size: 18px;
        }

        .badge-item-count {
            background: var(--primary-gradient);
            color: var(--bg-dark);
            padding: 2px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        /* ====== ITEMS TABLE ====== */
        .items-table-wrapper {
            background: var(--card);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .items-table-wrapper .table-header {
            padding: 16px 20px;
            background: #f8fafc;
            border-bottom: 2px solid var(--primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .items-table-wrapper .table-header h5 {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .items-table-wrapper .table-header h5 i {
            color: var(--primary);
        }

        .table-transaksi {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .table-transaksi thead th {
            padding: 10px 16px;
            text-align: left;
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border);
            background: #f8fafc;
        }

        .table-transaksi tbody td {
            padding: 10px 16px;
            border-bottom: 1px solid var(--border-light);
        }

        .table-transaksi tbody tr:hover {
            background: var(--card-hover);
        }

        .table-transaksi tbody tr:last-child td {
            border-bottom: none;
        }

        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }

        .table-warning {
            background: var(--primary-light) !important;
        }

        .table-warning td {
            font-weight: 700 !important;
            border-top: 2px solid var(--primary) !important;
        }

        /* ====== PRINT STYLE ====== */
        @media print {
            .sidebar,
            .sidebar-overlay,
            .sidebar-toggle,
            .topbar-right .btn-logout,
            .topbar-left .menu-toggle,
            .action-buttons,
            .no-print {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
                padding: 10px !important;
                width: 100% !important;
            }

            .topbar {
                display: none !important;
            }

            .detail-wrapper {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 15px !important;
            }

            .struk-wrapper,
            .info-card,
            .items-table-wrapper {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
                background: white !important;
            }

            .struk-container {
                border: none !important;
                box-shadow: none !important;
                padding: 5px !important;
            }

            .table-transaksi thead th {
                background: #f1f5f9 !important;
            }

            .table-warning {
                background: #fef3c7 !important;
            }

            @page {
                size: A4 portrait;
                margin: 10mm;
            }
        }

        /* ====== RESPONSIVE ====== */
        @media (max-width: 1024px) {
            .detail-wrapper {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .struk-container {
                width: 100%;
                max-width: 80mm;
            }

            .items-table-wrapper .table-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 480px) {
            .struk-wrapper {
                padding: 12px;
            }

            .info-card {
                padding: 14px 16px;
            }

            .items-table-wrapper .table-header {
                padding: 12px 14px;
            }

            .table-transaksi thead th,
            .table-transaksi tbody td {
                padding: 6px 10px;
                font-size: 11px;
            }

            .info-item .value {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>

    <!-- ============ SIDEBAR ============ -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../../assets/img/logo-sidebar.png" alt="Tuklife" class="logo-img" />
            <div class="brand">Tuklife <span>Admin</span></div>
        </div>
        <ul class="sidebar-nav">
            <li><a href="../dashboard.php"><i class="fas fa-chart-pie"></i><span class="nav-label">Dashboard</span></a></li>
            <li><a href="transaksi.php" class="active"><i class="fas fa-receipt"></i><span class="nav-label">Semua Transaksi</span></a></li>
            <li><a href="../laporan.php"><i class="fas fa-file-alt"></i><span class="nav-label">Laporan</span></a></li>
            <li><a href="../menu/menu.php"><i class="fas fa-utensils"></i><span class="nav-label">Kelola Menu</span></a></li>
            <li><a href="../stok.php"><i class="fas fa-boxes"></i><span class="nav-label">Kelola Stok</span></a></li>
            <li><a href="../kasir.php"><i class="fas fa-users"></i><span class="nav-label">Kelola Kasir</span></a></li>
            <li class="nav-divider"></li>
            <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span class="nav-label">Logout</span></a></li>
        </ul>
        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'] ?? 'A', 0, 1)) ?></div>
                <div>
                    <div class="user-name"><?= htmlspecialchars($_SESSION['nama'] ?? 'Admin') ?></div>
                    <div class="user-role">Administrator</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- ============ OVERLAY ============ -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>

    <!-- ============ MAIN CONTENT ============ -->
    <main class="main-content">

        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
                <h1><i class="fas fa-file-invoice"></i> Detail Transaksi</h1>
            </div>
            <div class="topbar-right">
                <span class="datetime"><i class="far fa-clock"></i> <span id="clockText"></span></span>
                <a href="../../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <?php if (!$transaksi): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> Transaksi tidak ditemukan.
            </div>
        <?php else: ?>

        <!-- ====== DETAIL WRAPPER ====== -->
        <div class="detail-wrapper">

            <!-- ====== STRUK ====== -->
            <div class="struk-wrapper">
                <div class="struk-label no-print">
                    <i class="fas fa-receipt"></i> STRUK PEMBELIAN
                </div>
                <div class="struk-container">
                    <div class="struk-header">
                        <img src="../../assets/img/kasir-struk.png" style="width: 90%; height: 50px; object-fit: contain;" alt="Logo">
                        <p>#YourDelightStreetDoze</p>
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
                        <?php 
                        // Reset pointer untuk loop ulang
                        mysqli_data_seek($qDetail, 0);
                        while($d = mysqli_fetch_assoc($qDetail)): 
                        ?>
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
                        <strong><i class="fab fa-instagram"></i> tuklife.street</strong> |
                        <strong><i class="fab fa-tiktok"></i> tuklife.street</strong>
                    </div>
                </div>
            </div>

            <!-- ====== INFO & DETAIL ====== -->
            <div>

                <!-- Info Transaksi -->
                <div class="info-card">
                    <div class="card-title">
                        <i class="fas fa-info-circle"></i> Informasi Transaksi
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="label">No. Transaksi</span>
                            <span class="value">#TRX-<?= str_pad($transaksi['id_transaksi'], 5, '0', STR_PAD_LEFT) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Tanggal</span>
                            <span class="value"><?= date('d/m/Y H:i', strtotime($transaksi['tgl_transaksi'])) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Kasir</span>
                            <span class="value"><?= htmlspecialchars($nama_kasir) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Pemesan</span>
                            <span class="value"><?= htmlspecialchars($transaksi['nama_pemesan'] ?? '-') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Total Item</span>
                            <span class="value">
                                <span class="badge-item-count">
                                    <?= mysqli_num_rows($qDetail) ?> item
                                </span>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="label">Total Pembayaran</span>
                            <span class="value total"><?= rupiah($transaksi['total']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Detail Items -->
                <div class="items-table-wrapper">
                    <div class="table-header">
                        <h5><i class="fas fa-list"></i> Daftar Item</h5>
                        <span class="badge-count"><?= mysqli_num_rows($qDetail) ?> item</span>
                    </div>
                    <div class="table-wrapper">
                        <table class="table-transaksi">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Barang</th>
                                    <th class="text-center">Jumlah</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-end">Sub Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Reset pointer untuk loop ulang
                                mysqli_data_seek($qDetail, 0);
                                $no = 1;
                                while($d = mysqli_fetch_assoc($qDetail)): 
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($d['nama_barang']) ?></td>
                                        <td class="text-center">
                                            <span class="badge-count" style="background:#f1f5f9;color:#475569;"><?= $d['jumlah'] ?></span>
                                        </td>
                                        <td class="text-end"><?= rupiah($d['harga']) ?></td>
                                        <td class="text-end fw-bold"><?= rupiah($d['sub_total']) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                <tr class="table-warning">
                                    <td colspan="4" class="text-end" style="font-weight:700;">TOTAL</td>
                                    <td class="text-end" style="font-weight:700;"><?= rupiah($transaksi['total']) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>

        <!-- ============ ACTION BUTTONS ============ -->
        <div class="action-buttons no-print" style="margin-top:24px;">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Cetak Struk
            </button>
            <a href="transaksi.php" class="btn btn-blue">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <?php endif; ?>

    </main>

    <!-- ============ JAVASCRIPT ============ -->
    <script>
    (function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const menuToggle = document.getElementById('menuToggle');
        const sidebarToggle = document.getElementById('sidebarToggle');

        function toggleSidebar() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
        }

        if (menuToggle) menuToggle.addEventListener('click', toggleSidebar);
        if (sidebarToggle) sidebarToggle.addEventListener('click', toggleSidebar);
        if (overlay) overlay.addEventListener('click', function() {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        });

        function updateClock() {
            const now = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const d = String(now.getDate()).padStart(2, '0');
            const m = String(now.getMonth() + 1).padStart(2, '0');
            const y = now.getFullYear();
            const h = String(now.getHours()).padStart(2, '0');
            const min = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            const el = document.getElementById('clockText');
            if (el) el.textContent = days[now.getDay()] + ', ' + d + '/' + m + '/' + y + ' ' + h + ':' + min + ':' + s;
        }
        updateClock();
        setInterval(updateClock, 1000);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    })();
    </script>

</body>
</html>