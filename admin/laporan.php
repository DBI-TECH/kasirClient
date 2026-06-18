<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../config/database.php';
require_once '../includes/fungsi.php';

$filter = $_GET['filter'] ?? 'hari';
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

$where = "1=1";
if ($filter == 'hari') {
    $where = "DATE(tgl_transaksi) = CURDATE()";
} elseif ($filter == 'minggu') {
    $where = "tgl_transaksi >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($filter == 'bulan') {
    $where = "MONTH(tgl_transaksi) = MONTH(CURDATE()) AND YEAR(tgl_transaksi) = YEAR(CURDATE())";
} elseif ($filter == 'custom') {
    $where = "DATE(tgl_transaksi) BETWEEN '$start_date' AND '$end_date'";
}

$query = "SELECT t.*, k.nama_kasir 
          FROM transaksi t
          LEFT JOIN kasir k ON k.id_kasir = t.id_kasir
          WHERE $where 
          ORDER BY t.id_transaksi DESC";
$result = mysqli_query($conn, $query);

$totalPendapatan = 0;
$totalTransaksi = 0;
$dataTransaksi = [];
while ($row = mysqli_fetch_assoc($result)) {
    $totalPendapatan += $row['total'];
    $totalTransaksi++;
    $dataTransaksi[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes" />
    <title>Laporan - Admin Tuklife</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/admin-style.css" />
</head>
<body>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../assets/img/logo-sidebar.png" alt="Tuklife" class="logo-img" />
            <div class="brand">Tuklife <span>Admin</span></div>
        </div>
        <ul class="sidebar-nav">
            <li><a href="dashboard.php"><i class="fas fa-chart-pie"></i><span class="nav-label">Dashboard</span></a></li>
            <li><a href="transaksi/transaksi.php"><i class="fas fa-receipt"></i><span class="nav-label">Semua Transaksi</span></a></li>
            <li><a href="laporan.php" class="active"><i class="fas fa-file-alt"></i><span class="nav-label">Laporan</span></a></li>
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

    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>

    <main class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
                <h1><i class="fas fa-file-alt"></i> Laporan Transaksi</h1>
            </div>
            <div class="topbar-right">
                <span class="datetime"><i class="far fa-clock"></i> <span id="clockText"></span></span>
                <a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <!-- Filter -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-filter"></i> Filter Laporan</h3>
            </div>
            <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end;">
                <div class="form-group" style="margin-bottom:0;">
                    <label>Periode</label>
                    <select name="filter" id="filter" onchange="toggleCustomDate()">
                        <option value="hari" <?= $filter == 'hari' ? 'selected' : '' ?>>Hari Ini</option>
                        <option value="minggu" <?= $filter == 'minggu' ? 'selected' : '' ?>>7 Hari Terakhir</option>
                        <option value="bulan" <?= $filter == 'bulan' ? 'selected' : '' ?>>Bulan Ini</option>
                        <option value="custom" <?= $filter == 'custom' ? 'selected' : '' ?>>Custom</option>
                    </select>
                </div>
                <div class="form-group" id="dateRange" style="margin-bottom:0;<?= $filter == 'custom' ? '' : 'display:none;' ?>">
                    <label>Mulai</label>
                    <input type="date" name="start_date" value="<?= $start_date ?>" style="max-width:160px;">
                </div>
                <div class="form-group" id="dateRangeEnd" style="margin-bottom:0;<?= $filter == 'custom' ? '' : 'display:none;' ?>">
                    <label>Selesai</label>
                    <input type="date" name="end_date" value="<?= $end_date ?>" style="max-width:160px;">
                </div>
                <button type="submit" class="btn btn-primary" style="padding:10px 20px;">
                    <i class="fas fa-search"></i> Tampilkan
                </button>
            </form>
        </div>

        <!-- Summary -->
        <div class="stats-grid" style="margin-bottom:20px;">
            <div class="stat-card">
                <div class="stat-value"><?= $totalTransaksi ?></div>
                <div class="stat-label"><i class="fas fa-receipt"></i> Total Transaksi</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= rupiah($totalPendapatan) ?></div>
                <div class="stat-label"><i class="fas fa-money-bill-wave"></i> Total Pendapatan</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $totalTransaksi > 0 ? rupiah($totalPendapatan / $totalTransaksi) : 'Rp 0' ?></div>
                <div class="stat-label"><i class="fas fa-calculator"></i> Rata-rata per Transaksi</div>
            </div>
        </div>

        <!-- Tabel -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-list"></i> Daftar Transaksi</h3>
                <span class="badge-count"><?= $totalTransaksi ?> transaksi</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Pemesan</th>
                            <th>Kasir</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">Cash</th>
                            <th class="text-right">Kembalian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($dataTransaksi)): ?>
                            <?php $no = 1; foreach ($dataTransaksi as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($row['tgl_transaksi'])) ?></td>
                                <td><?= htmlspecialchars($row['nama_pemesan'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['nama_kasir'] ?? '-') ?></td>
                                <td class="text-right"><strong><?= rupiah($row['total']) ?></strong></td>
                                <td class="text-right"><?= rupiah($row['cash']) ?></td>
                                <td class="text-right"><?= rupiah($row['change']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align:center;padding:30px;color:#94a3b8;">
                                    <i class="fas fa-info-circle"></i> Tidak ada transaksi pada periode ini
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="action-buttons">
            <button onclick="window.print()" class="btn btn-green"><i class="fas fa-print"></i> Cetak Laporan</button>
            <a href="dashboard.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </main>

    <script>
    function toggleCustomDate() {
        const filter = document.getElementById('filter').value;
        document.getElementById('dateRange').style.display = filter === 'custom' ? 'block' : 'none';
        document.getElementById('dateRangeEnd').style.display = filter === 'custom' ? 'block' : 'none';
    }

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
    })();
    </script>

</body>
</html>