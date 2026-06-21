<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Include database.php dulu sebelum fungsi.php
require_once '../config/database.php';
require_once '../includes/fungsi.php';

// Ambil data transaksi terbaru untuk ditampilkan
$queryTransaksiTerbaru = "SELECT t.*, k.nama_kasir 
                          FROM transaksi t
                          LEFT JOIN kasir k ON k.id_kasir = t.id_kasir
                          ORDER BY t.id_transaksi DESC 
                          LIMIT 5";
$transaksiTerbaru = mysqli_query($conn, $queryTransaksiTerbaru);

// Ambil produk terlaris
$produkTerlaris = getProdukTerlaris($conn, 5);

// ========== AMBIL DATA GRAFIK ==========
// Grafik penjualan 7 hari terakhir
$grafik7Hari = getGrafikPenjualan($conn, 7);
$labels7Hari = [];
$dataTransaksi7Hari = [];
$dataPendapatan7Hari = [];

while ($row = mysqli_fetch_assoc($grafik7Hari)) {
    $labels7Hari[] = date('d/m', strtotime($row['tanggal']));
    $dataTransaksi7Hari[] = (int)$row['jumlah_transaksi'];
    $dataPendapatan7Hari[] = (int)$row['pendapatan'];
}

// Grafik penjualan 30 hari terakhir
$grafik30Hari = getGrafikPenjualan($conn, 30);
$labels30Hari = [];
$dataPendapatan30Hari = [];

while ($row = mysqli_fetch_assoc($grafik30Hari)) {
    $labels30Hari[] = date('d/m', strtotime($row['tanggal']));
    $dataPendapatan30Hari[] = (int)$row['pendapatan'];
}

// Statistik keuntungan
$totalPendapatan = getPendapatanTotal($conn);
$pendapatanBulanIni = getPendapatanBulanIni($conn);
$pendapatanHariIni = getPendapatanHariIni($conn);
$totalTransaksi = getTotalTransaksiHariIni($conn);

// Hitung rata-rata per hari (30 hari)
$avgPerHari = count($dataPendapatan30Hari) > 0 ? round(array_sum($dataPendapatan30Hari) / count($dataPendapatan30Hari)) : 0;

// Prediksi keuntungan bulan ini (estimasi)
$hariDalamBulan = date('t');
$hariBulanIni = date('j');
$prediksiBulanIni = $hariBulanIni > 0 ? round(($pendapatanBulanIni / $hariBulanIni) * $hariDalamBulan) : 0;

// Grafik per kategori (untuk pie chart)
$queryKategori = "SELECT b.tipe, SUM(dt.jumlah * b.harga) as total_penjualan
                  FROM detail_transaksi dt
                  JOIN barang b ON b.id_barang = dt.id_barang
                  GROUP BY b.tipe
                  ORDER BY total_penjualan DESC";
$resultKategori = mysqli_query($conn, $queryKategori);
$kategoriLabels = [];
$kategoriData = [];
$kategoriColors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];

while ($row = mysqli_fetch_assoc($resultKategori)) {
    $tipe = $row['tipe'] ?? 'Lainnya';
    $kategoriLabels[] = ucfirst($tipe);
    $kategoriData[] = (int)$row['total_penjualan'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes" />
    <title>Dashboard Admin - Tuklife</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>assets/img/logo-sidebar.png">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/admin-style.css" />
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* ===== GRAFIK STYLES ===== */
        .chart-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 28px;
        }
        .chart-card {
            background: var(--card);
            border-radius: var(--radius);
            padding: 20px 20px 10px 20px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        .chart-card:hover {
            box-shadow: var(--shadow-hover);
        }
        .chart-card .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 8px;
        }
        .chart-card .chart-header h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .chart-card .chart-header h4 i {
            color: var(--primary);
        }
        .chart-card .chart-header .chart-badge {
            font-size: 11px;
            padding: 3px 12px;
            border-radius: 20px;
            background: var(--primary-light);
            color: var(--primary-dark);
            font-weight: 500;
        }
        .chart-card .chart-wrapper {
            position: relative;
            height: 220px;
            width: 100%;
        }
        .chart-card .chart-wrapper.pie-chart {
            height: 200px;
        }
        .chart-card .chart-wrapper canvas {
            width: 100% !important;
            height: 100% !important;
        }

        /* ===== PROFIT STATS ===== */
        .profit-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }
        .profit-card {
            background: var(--card);
            padding: 16px 18px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        .profit-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }
        .profit-card.gold::before { background: var(--primary-gradient); }
        .profit-card.green::before { background: linear-gradient(135deg, #10b981, #059669); }
        .profit-card.blue::before { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .profit-card.purple::before { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .profit-card.orange::before { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .profit-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
        }
        .profit-card .profit-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--text);
            line-height: 1.2;
        }
        .profit-card .profit-value .currency {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-secondary);
        }
        .profit-card .profit-label {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .profit-card .profit-trend {
            font-size: 11px;
            font-weight: 600;
            margin-top: 4px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .profit-card .profit-trend.up { color: #10b981; }
        .profit-card .profit-trend.down { color: #ef4444; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .chart-grid {
                grid-template-columns: 1fr 1fr;
            }
            .profit-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            .profit-card .profit-value {
                font-size: 20px;
            }
        }
        @media (max-width: 768px) {
            .chart-grid {
                grid-template-columns: 1fr;
            }
            .chart-card .chart-wrapper {
                height: 200px;
            }
            .chart-card .chart-wrapper.pie-chart {
                height: 180px;
            }
            .profit-stats {
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }
            .profit-card {
                padding: 14px 14px;
            }
            .profit-card .profit-value {
                font-size: 18px;
            }
        }
        @media (max-width: 480px) {
            .profit-stats {
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }
            .profit-card {
                padding: 10px 12px;
            }
            .profit-card .profit-value {
                font-size: 15px;
            }
            .profit-card .profit-label {
                font-size: 10px;
            }
            .profit-card .profit-trend {
                font-size: 10px;
            }
            .chart-card {
                padding: 14px 14px 6px 14px;
            }
            .chart-card .chart-wrapper {
                height: 170px;
            }
            .chart-card .chart-wrapper.pie-chart {
                height: 150px;
            }
            .chart-card .chart-header h4 {
                font-size: 12px;
            }
            .chart-card .chart-header .chart-badge {
                font-size: 9px;
                padding: 2px 10px;
            }
        }
        @media (max-width: 360px) {
            .profit-stats {
                grid-template-columns: 1fr 1fr;
                gap: 6px;
            }
            .profit-card {
                padding: 8px 10px;
            }
            .profit-card .profit-value {
                font-size: 13px;
            }
            .profit-card .profit-label {
                font-size: 9px;
            }
            .profit-card .profit-trend {
                font-size: 9px;
            }
        }
    </style>
</head>
<body>

    <!-- ============ SIDEBAR ============ -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../assets/img/logo-sidebar.png" alt="Tuklife" class="logo-img" />
            <div class="brand">
                Tuklife
                <span>Admin</span>
            </div>
        </div>
        <ul class="sidebar-nav">
            <li><a href="dashboard.php" class="active"><i class="fas fa-chart-pie"></i><span class="nav-label">Dashboard</span></a></li>
            <li><a href="transaksi/transaksi.php"><i class="fas fa-receipt"></i><span class="nav-label">Semua Transaksi</span></a></li>
            <li><a href="laporan.php"><i class="fas fa-file-alt"></i><span class="nav-label">Laporan</span></a></li>
            <li><a href="menu/menu.php"><i class="fas fa-utensils"></i><span class="nav-label">Kelola Menu</span></a></li>
            <li><a href="stok.php"><i class="fas fa-boxes"></i><span class="nav-label">Kelola Stok</span></a></li>
            <li><a href="kasir.php"><i class="fas fa-users"></i><span class="nav-label">Kelola Kasir</span></a></li>
            <li class="nav-divider"></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span class="nav-label">Logout</span></a></li>
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

    <!-- ============ TOGGLE ============ -->
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle Sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <!-- ============ MAIN CONTENT ============ -->
    <main class="main-content">

        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>
                    <i class="fas fa-chart-pie"></i>
                    <span>Dashboard Admin</span>
                </h1>
            </div>
            <div class="topbar-right">
                <span class="datetime">
                    <i class="far fa-clock"></i>
                    <span id="clockText"></span>
                </span>
                <a href="../logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <?php
        $stokMenipis = getStokMenipis($conn, 10);
        if ($stokMenipis && mysqli_num_rows($stokMenipis) > 0):
        ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>⚠️ Peringatan Stok!</strong>
                Terdapat <strong><?= mysqli_num_rows($stokMenipis) ?></strong> menu dengan stok menipis (≤ 10).
                <a href="stok.php">Kelola Stok →</a>
            </div>
        </div>
        <?php endif; ?>

        <!-- ============ STATS ============ -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= rupiah($pendapatanHariIni) ?></div>
                <div class="stat-label"><i class="fas fa-calendar-day"></i> Pendapatan Hari Ini</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= rupiah($pendapatanBulanIni) ?></div>
                <div class="stat-label"><i class="fas fa-calendar-alt"></i> Pendapatan Bulan Ini</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= rupiah($totalPendapatan) ?></div>
                <div class="stat-label"><i class="fas fa-money-bill-wave"></i> Total Pendapatan</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $totalTransaksi ?></div>
                <div class="stat-label"><i class="fas fa-receipt"></i> Transaksi Hari Ini</div>
            </div>
        </div>

        <!-- ============ PROFIT / KEUNTUNGAN STATS ============ -->
        <div class="profit-stats">
            <div class="profit-card gold">
                <div class="profit-value">
                    <span class="currency">Rp</span> <?= number_format($pendapatanBulanIni, 0, ',', '.') ?>
                </div>
                <div class="profit-label"><i class="fas fa-calendar-alt"></i> Pendapatan Bulan Ini</div>
                <div class="profit-trend up">
                    <i class="fas fa-arrow-up"></i> 
                    <?= $hariBulanIni > 0 ? round(($pendapatanBulanIni / $hariBulanIni) * 30) : 0 ?>/hari
                </div>
            </div>
            <div class="profit-card green">
                <div class="profit-value">
                    <span class="currency">Rp</span> <?= number_format($avgPerHari, 0, ',', '.') ?>
                </div>
                <div class="profit-label"><i class="fas fa-chart-line"></i> Rata-rata 30 Hari</div>
                <div class="profit-trend up">
                    <i class="fas fa-arrow-up"></i> per hari
                </div>
            </div>
            <div class="profit-card blue">
                <div class="profit-value">
                    <span class="currency">Rp</span> <?= number_format($prediksiBulanIni, 0, ',', '.') ?>
                </div>
                <div class="profit-label"><i class="fas fa-chart-simple"></i> Prediksi Bulan Ini</div>
                <div class="profit-trend up">
                    <i class="fas fa-arrow-up"></i> estimasi
                </div>
            </div>
            <div class="profit-card purple">
                <div class="profit-value">
                    <?= count($dataTransaksi7Hari) > 0 ? array_sum($dataTransaksi7Hari) : 0 ?>
                </div>
                <div class="profit-label"><i class="fas fa-receipt"></i> Transaksi 7 Hari</div>
                <div class="profit-trend up">
                    <i class="fas fa-arrow-up"></i> 
                    <?= count($dataTransaksi7Hari) > 0 ? round(array_sum($dataTransaksi7Hari) / count($dataTransaksi7Hari)) : 0 ?>/hari
                </div>
            </div>
        </div>

        <!-- ============ CHART SECTION ============ -->
        <div class="chart-grid">

            <!-- Grafik Penjualan 7 Hari (Bar Chart) -->
            <div class="chart-card">
                <div class="chart-header">
                    <h4><i class="fas fa-chart-bar"></i> Penjualan 7 Hari</h4>
                    <span class="chart-badge"><i class="far fa-calendar"></i> Minggu ini</span>
                </div>
                <div class="chart-wrapper">
                    <canvas id="salesChart7Days"></canvas>
                </div>
            </div>

            <!-- Grafik Pendapatan 30 Hari (Line Chart) -->
            <div class="chart-card">
                <div class="chart-header">
                    <h4><i class="fas fa-chart-line"></i> Pendapatan 30 Hari</h4>
                    <span class="chart-badge"><i class="far fa-calendar"></i> Bulan ini</span>
                </div>
                <div class="chart-wrapper">
                    <canvas id="revenueChart30Days"></canvas>
                </div>
            </div>

            <!-- Grafik Kategori Penjualan (Pie Chart) -->
            <div class="chart-card">
                <div class="chart-header">
                    <h4><i class="fas fa-chart-pie"></i> Penjualan per Kategori</h4>
                    <span class="chart-badge"><i class="fas fa-tags"></i> Total</span>
                </div>
                <div class="chart-wrapper pie-chart">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>

            <!-- Grafik Transaksi vs Pendapatan (Dual Chart) -->
            <div class="chart-card">
                <div class="chart-header">
                    <h4><i class="fas fa-chart-simple"></i> Transaksi & Pendapatan</h4>
                    <span class="chart-badge"><i class="far fa-calendar"></i> 7 Hari</span>
                </div>
                <div class="chart-wrapper">
                    <canvas id="dualChart"></canvas>
                </div>
            </div>

        </div>

        <!-- ============ SECTION GRID ============ -->
        <div class="section-grid">

            <!-- ===== TRANSAKSI TERBARU ===== -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-clock"></i> Transaksi Terbaru</h3>
                    <a href="transaksi/transaksi.php" class="action-link">Lihat Semua →</a>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Pemesan</th>
                                <th>Kasir</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($transaksiTerbaru) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($transaksiTerbaru)): ?>
                                <tr>
                                    <td><?= date('d/m H:i', strtotime($row['tgl_transaksi'])) ?></td>
                                    <td><?= htmlspecialchars($row['nama_pemesan'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['nama_kasir'] ?? '-') ?></td>
                                    <td class="text-right"><strong><?= rupiah($row['total']) ?></strong></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align:center;color:#94a3b8;padding:20px 0;">
                                        <i class="fas fa-info-circle"></i> Belum ada transaksi
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ===== PRODUK TERLARIS ===== -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-trophy"></i> Produk Terlaris</h3>
                    <span class="badge-count">Top 5</span>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Menu</th>
                                <th class="text-right">Terjual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($produkTerlaris && mysqli_num_rows($produkTerlaris) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($produkTerlaris)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                    <td class="text-right"><strong><?= $row['total_terjual'] ?></strong></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" style="text-align:center;color:#94a3b8;padding:20px 0;">
                                        <i class="fas fa-info-circle"></i> Belum ada data
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ============ ACTION BUTTONS ============ -->
        <div class="action-buttons">
            <a href="laporan.php" class="btn btn-primary">
                <i class="fas fa-file-alt"></i> Lihat Laporan Lengkap
            </a>
            <a href="stok.php" class="btn btn-green">
                <i class="fas fa-boxes"></i> Kelola Stok
            </a>
            <a href="menu/menu.php" class="btn btn-blue">
                <i class="fas fa-utensils"></i> Kelola Menu
            </a>
        </div>

    </main>

    <!-- ============ JAVASCRIPT ============ -->
    <script>
    (function() {
        'use strict';

        // ===== SIDEBAR TOGGLE =====
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const menuToggle = document.getElementById('menuToggle');
        const sidebarToggle = document.getElementById('sidebarToggle');

        function toggleSidebar() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
        }

        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        if (menuToggle) menuToggle.addEventListener('click', toggleSidebar);
        if (sidebarToggle) sidebarToggle.addEventListener('click', toggleSidebar);
        if (overlay) overlay.addEventListener('click', closeSidebar);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('open')) closeSidebar();
        });

        // ===== LIVE CLOCK =====
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
            if (el) {
                el.textContent = days[now.getDay()] + ', ' + d + '/' + m + '/' + y + ' ' + h + ':' + min + ':' + s;
            }
        }
        updateClock();
        setInterval(updateClock, 1000);

        // ===== AUTO CLOSE ALERT =====
        document.querySelectorAll('.alert').forEach(function(alert) {
            setTimeout(function() {
                alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(function() {
                    if (alert.parentNode) alert.remove();
                }, 500);
            }, 6000);
        });

        // ===== CHART.JS =====
        // Data dari PHP
        const labels7 = <?= json_encode($labels7Hari) ?>;
        const transaksiData7 = <?= json_encode($dataTransaksi7Hari) ?>;
        const pendapatanData7 = <?= json_encode($dataPendapatan7Hari) ?>;
        const labels30 = <?= json_encode($labels30Hari) ?>;
        const pendapatanData30 = <?= json_encode($dataPendapatan30Hari) ?>;
        const kategoriLabels = <?= json_encode($kategoriLabels) ?>;
        const kategoriData = <?= json_encode($kategoriData) ?>;
        const kategoriColors = <?= json_encode(array_slice($kategoriColors, 0, count($kategoriLabels))) ?>;

        // Warna default
        const defaultColors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#FF6384'];

        // ---- Chart 1: Penjualan 7 Hari (Bar) ----
        if (document.getElementById('salesChart7Days')) {
            new Chart(document.getElementById('salesChart7Days'), {
                type: 'bar',
                data: {
                    labels: labels7,
                    datasets: [{
                        label: 'Jumlah Transaksi',
                        data: transaksiData7,
                        backgroundColor: 'rgba(255, 204, 0, 0.7)',
                        borderColor: '#e6b800',
                        borderWidth: 2,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' transaksi';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                font: { size: 10 }
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: { size: 9 }
                            }
                        }
                    }
                }
            });
        }

        // ---- Chart 2: Pendapatan 30 Hari (Line) ----
        if (document.getElementById('revenueChart30Days')) {
            new Chart(document.getElementById('revenueChart30Days'), {
                type: 'line',
                data: {
                    labels: labels30,
                    datasets: [{
                        label: 'Pendapatan',
                        data: pendapatanData30,
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderColor: '#10b981',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 3,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                font: { size: 9 },
                                callback: function(value) {
                                    if (value >= 1000) return (value/1000) + 'k';
                                    return value;
                                }
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: { size: 8 },
                                maxTicksLimit: 15
                            }
                        }
                    }
                }
            });
        }

        // ---- Chart 3: Kategori Penjualan (Pie) ----
        if (document.getElementById('categoryChart')) {
            new Chart(document.getElementById('categoryChart'), {
                type: 'doughnut',
                data: {
                    labels: kategoriLabels,
                    datasets: [{
                        data: kategoriData,
                        backgroundColor: kategoriColors.length > 0 ? kategoriColors : defaultColors,
                        borderWidth: 2,
                        borderColor: '#fff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: { size: 10 },
                                boxWidth: 12,
                                padding: 8
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    let percentage = total > 0 ? (context.parsed / total * 100).toFixed(1) : 0;
                                    return context.label + ': Rp ' + context.parsed.toLocaleString('id-ID') + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        // ---- Chart 4: Dual Chart (Transaksi & Pendapatan) ----
        if (document.getElementById('dualChart')) {
            new Chart(document.getElementById('dualChart'), {
                type: 'bar',
                data: {
                    labels: labels7,
                    datasets: [
                        {
                            label: 'Transaksi',
                            data: transaksiData7,
                            backgroundColor: 'rgba(59, 130, 246, 0.6)',
                            borderColor: '#3b82f6',
                            borderWidth: 1,
                            borderRadius: 3,
                            order: 2,
                            yAxisID: 'y1',
                        },
                        {
                            label: 'Pendapatan (Rp)',
                            data: pendapatanData7,
                            type: 'line',
                            backgroundColor: 'rgba(255, 204, 0, 0.1)',
                            borderColor: '#ffcc00',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: '#ffcc00',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            order: 1,
                            yAxisID: 'y',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: { size: 10 },
                                boxWidth: 12,
                                padding: 8
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    if (context.dataset.label === 'Pendapatan (Rp)') {
                                        return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                    }
                                    return context.parsed.y + ' transaksi';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            position: 'left',
                            ticks: {
                                font: { size: 9 },
                                callback: function(value) {
                                    if (value >= 1000) return (value/1000) + 'k';
                                    return value;
                                }
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            ticks: {
                                font: { size: 9 },
                                stepSize: 1
                            },
                            grid: {
                                display: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: { size: 9 }
                            }
                        }
                    }
                }
            });
        }

        // ===== RESPONSIVE CHART RESIZE =====
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                // Chart.js otomatis meresize
            }, 200);
        });

    })();
    </script>

</body>
</html>