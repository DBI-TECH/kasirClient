<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../config/database.php';
require_once '../includes/fungsi.php';

// Filter
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

$query = "SELECT * FROM transaksi WHERE $where ORDER BY id_transaksi DESC";
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Admin Tuklife</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; }
        
        .admin-wrapper { display: flex; min-height: 100vh; }
        
        .sidebar {
            width: 260px;
            background: #1a1a2e;
            color: #fff;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar .logo { text-align: center; padding: 0 20px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar .logo img { max-width: 120px; height: auto; }
        .sidebar .logo h3 { color: #ffcc00; margin-top: 10px; font-size: 18px; }
        .sidebar ul { list-style: none; padding: 20px 0; }
        .sidebar ul li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 24px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.3s;
            font-size: 14px;
        }
        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background: rgba(255,204,0,0.15);
            color: #ffcc00;
        }
        .sidebar ul li a i { width: 20px; text-align: center; }
        
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 24px;
        }
        
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #e4e9f2;
        }
        
        .top-bar h1 { font-size: 24px; color: #1a1a2e; }
        
        .btn-logout {
            padding: 8px 20px;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: 0.2s;
            text-decoration: none;
        }
        .btn-logout:hover { background: #dc2626; }
        
        .card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            border: 1px solid #e4e9f2;
            margin-bottom: 20px;
        }
        
        .card h3 {
            font-size: 16px;
            color: #1a1a2e;
            margin-bottom: 16px;
        }
        
        .filter-form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: end;
        }
        
        .filter-form .form-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .filter-form .form-group label {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
        }
        
        .filter-form .form-group input,
        .filter-form .form-group select {
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 13px;
        }
        
        .filter-form .form-group input:focus,
        .filter-form .form-group select:focus {
            outline: none;
            border-color: #ffcc00;
        }
        
        .btn-filter {
            padding: 8px 20px;
            background: #ffcc00;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            color: #1a1a2e;
        }
        
        .btn-filter:hover { background: #e6b800; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        
        table th,
        table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9;
            text-align: left;
        }
        
        table th {
            background: #f8fafc;
            font-weight: 600;
            color: #64748b;
            font-size: 12px;
            text-transform: uppercase;
        }
        
        .text-right { text-align: right; }
        
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
        
        .summary-stat {
            background: white;
            padding: 16px;
            border-radius: 12px;
            border: 1px solid #e4e9f2;
            text-align: center;
        }
        
        .summary-stat .value {
            font-size: 22px;
            font-weight: 700;
            color: #ffcc00;
        }
        
        .summary-stat .label {
            font-size: 12px;
            color: #64748b;
        }
        
        @media (max-width: 768px) {
            .sidebar { width: 200px; }
            .main-content { margin-left: 200px; padding: 16px; }
            .filter-form { flex-direction: column; align-items: stretch; }
        }
        
        @media (max-width: 480px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <aside class="sidebar">
        <div class="logo">
            <img src="../assets/img/logop2.png" alt="Tuklife">
            <h3>Admin Panel</h3>
        </div>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="transaksi.php"><i class="fas fa-receipt"></i> Semua Transaksi</a></li>
            <li><a href="laporan.php" class="active"><i class="fas fa-file-alt"></i> Laporan</a></li>
            <li><a href="menu.php"><i class="fas fa-utensils"></i> Kelola Menu</a></li>
            <li><a href="stok.php"><i class="fas fa-boxes"></i> Kelola Stok</a></li>
            <li><a href="kasir.php"><i class="fas fa-users"></i> Kelola Kasir</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    
    <main class="main-content">
        <div class="top-bar">
            <h1><i class="fas fa-file-alt"></i> Laporan Transaksi</h1>
            <a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        
        <!-- Filter -->
        <div class="card">
            <h3><i class="fas fa-filter"></i> Filter Laporan</h3>
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label>Periode</label>
                    <select name="filter" id="filter" onchange="toggleCustomDate()">
                        <option value="hari" <?= $filter == 'hari' ? 'selected' : '' ?>>Hari Ini</option>
                        <option value="minggu" <?= $filter == 'minggu' ? 'selected' : '' ?>>7 Hari Terakhir</option>
                        <option value="bulan" <?= $filter == 'bulan' ? 'selected' : '' ?>>Bulan Ini</option>
                        <option value="custom" <?= $filter == 'custom' ? 'selected' : '' ?>>Custom</option>
                    </select>
                </div>
                <div class="form-group" id="dateRange" style="<?= $filter == 'custom' ? '' : 'display:none;' ?>">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="start_date" value="<?= $start_date ?>">
                </div>
                <div class="form-group" id="dateRangeEnd" style="<?= $filter == 'custom' ? '' : 'display:none;' ?>">
                    <label>Tanggal Selesai</label>
                    <input type="date" name="end_date" value="<?= $end_date ?>">
                </div>
                <button type="submit" class="btn-filter"><i class="fas fa-search"></i> Tampilkan</button>
            </form>
        </div>
        
        <!-- Summary -->
        <div class="summary-stats">
            <div class="summary-stat">
                <div class="value"><?= $totalTransaksi ?></div>
                <div class="label"><i class="fas fa-receipt"></i> Total Transaksi</div>
            </div>
            <div class="summary-stat">
                <div class="value"><?= rupiah($totalPendapatan) ?></div>
                <div class="label"><i class="fas fa-money-bill-wave"></i> Total Pendapatan</div>
            </div>
            <div class="summary-stat">
                <div class="value"><?= $totalTransaksi > 0 ? rupiah($totalPendapatan / $totalTransaksi) : 'Rp 0' ?></div>
                <div class="label"><i class="fas fa-calculator"></i> Rata-rata per Transaksi</div>
            </div>
        </div>
        
        <!-- Tabel Transaksi -->
        <div class="card">
            <h3><i class="fas fa-list"></i> Daftar Transaksi</h3>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Pemesan</th>
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
                                <td class="text-right"><strong><?= rupiah($row['total']) ?></strong></td>
                                <td class="text-right"><?= rupiah($row['cash']) ?></td>
                                <td class="text-right"><?= rupiah($row['change']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align:center;padding:30px;color:#94a3b8;">
                                    <i class="fas fa-info-circle"></i> Tidak ada transaksi pada periode ini
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button onclick="window.print()" class="btn-filter" style="background:#10b981;color:white;">
                <i class="fas fa-print"></i> Cetak Laporan
            </button>
            <a href="dashboard.php" class="btn-filter" style="background:#64748b;color:white;text-decoration:none;">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        
    </main>
</div>

<script>
function toggleCustomDate() {
    const filter = document.getElementById('filter').value;
    const dateRange = document.getElementById('dateRange');
    const dateRangeEnd = document.getElementById('dateRangeEnd');
    if (filter === 'custom') {
        dateRange.style.display = 'block';
        dateRangeEnd.style.display = 'block';
    } else {
        dateRange.style.display = 'none';
        dateRangeEnd.style.display = 'none';
    }
}
</script>
</body>
</html>