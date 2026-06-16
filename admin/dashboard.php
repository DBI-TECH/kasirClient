<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../config/database.php';
require_once '../includes/fungsi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Tuklife</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; }
        
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* SIDEBAR */
        .sidebar {
            width: 260px;
            background: #1a1a2e;
            color: #fff;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar .logo {
            text-align: center;
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar .logo img {
            max-width: 120px;
            height: auto;
        }
        
        .sidebar .logo h3 {
            color: #ffcc00;
            margin-top: 10px;
            font-size: 18px;
        }
        
        .sidebar ul {
            list-style: none;
            padding: 20px 0;
        }
        
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
        
        .sidebar ul li a i {
            width: 20px;
            text-align: center;
        }
        
        /* MAIN CONTENT */
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
        
        .top-bar h1 {
            font-size: 24px;
            color: #1a1a2e;
        }
        
        .top-bar .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .top-bar .user-info span {
            color: #64748b;
        }
        
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
        
        .btn-logout:hover {
            background: #dc2626;
        }
        
        /* STATS GRID */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1px solid #e4e9f2;
        }
        
        .stat-card .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #ffcc00;
        }
        
        .stat-card .stat-label {
            font-size: 13px;
            color: #64748b;
            margin-top: 4px;
        }
        
        /* SECTION GRID */
        .section-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }
        
        .card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            border: 1px solid #e4e9f2;
        }
        
        .card h3 {
            font-size: 16px;
            color: #1a1a2e;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .card table {
            width: 100%;
            font-size: 13px;
            border-collapse: collapse;
        }
        
        .card table th,
        .card table td {
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
            text-align: left;
        }
        
        .card table th {
            font-weight: 600;
            color: #64748b;
            font-size: 12px;
            text-transform: uppercase;
        }
        
        .badge {
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }
        
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-secondary { background: #f1f5f9; color: #475569; }
        
        .text-right { text-align: right; }
        
        .btn-sm {
            padding: 4px 12px;
            font-size: 12px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary-sm {
            background: #ffcc00;
            color: #1a1a2e;
        }
        
        .btn-primary-sm:hover {
            background: #e6b800;
        }
        
        @media (max-width: 768px) {
            .sidebar { width: 200px; }
            .main-content { margin-left: 200px; padding: 16px; }
            .section-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }
        
        @media (max-width: 480px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
            .stats-grid { grid-template-columns: 1fr; }
        }
        
        .stok-warning {
            background: #fef3c7;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #f59e0b;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #92400e;
        }
        
        .stok-warning i {
            font-size: 20px;
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo">
            <img src="../assets/img/logop2.png" alt="Tuklife">
            <h3>Admin Panel</h3>
        </div>
        <ul>
            <li><a href="dashboard.php" class="active"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="transaksi.php"><i class="fas fa-receipt"></i> Semua Transaksi</a></li>
            <li><a href="laporan.php"><i class="fas fa-file-alt"></i> Laporan</a></li>
            <li><a href="menu.php"><i class="fas fa-utensils"></i> Kelola Menu</a></li>
            <li><a href="stok.php"><i class="fas fa-boxes"></i> Kelola Stok</a></li>
            <li><a href="kasir.php"><i class="fas fa-users"></i> Kelola Kasir</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="top-bar">
            <h1><i class="fas fa-chart-pie"></i> Dashboard Admin</h1>
            <div class="user-info">
                <span><i class="fas fa-user-shield"></i> <?= htmlspecialchars($_SESSION['nama'] ?? 'Admin') ?></span>
                <a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <?php
        // Cek stok menipis
        $stokMenipis = getStokMenipis($conn, 10);
        if ($stokMenipis && mysqli_num_rows($stokMenipis) > 0):
        ?>
        <div class="stok-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>⚠️ Peringatan Stok!</strong> 
                Terdapat <?= mysqli_num_rows($stokMenipis) ?> menu dengan stok menipis (≤ 10).
                <a href="stok.php" style="color:#92400e;font-weight:bold;">Kelola Stok →</a>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= rupiah(getPendapatanHariIni($conn)) ?></div>
                <div class="stat-label"><i class="fas fa-calendar-day"></i> Pendapatan Hari Ini</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= rupiah(getPendapatanBulanIni($conn)) ?></div>
                <div class="stat-label"><i class="fas fa-calendar-alt"></i> Pendapatan Bulan Ini</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= rupiah(getPendapatanTotal($conn)) ?></div>
                <div class="stat-label"><i class="fas fa-money-bill-wave"></i> Total Pendapatan</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= getTotalTransaksiHariIni($conn) ?></div>
                <div class="stat-label"><i class="fas fa-receipt"></i> Transaksi Hari Ini</div>
            </div>
        </div>
        
        <!-- Grafik & Produk Terlaris -->
        <div class="section-grid">
            <div class="card">
                <h3><i class="fas fa-chart-bar"></i> Grafik Penjualan (7 Hari)</h3>
                <div style="height:200px;display:flex;align-items:flex-end;gap:8px;padding-top:10px;">
                    <?php
                    // ===== PERBAIKAN: Handle error query grafik =====
                    $grafik = getGrafikPenjualan($conn, 7);
                    $maxPendapatan = 0;
                    $dataGrafik = [];
                    
                    if ($grafik) {
                        while($row = mysqli_fetch_assoc($grafik)) {
                            $dataGrafik[] = $row;
                            if ($row['pendapatan'] > $maxPendapatan) $maxPendapatan = $row['pendapatan'];
                        }
                    }
                    
                    if ($maxPendapatan == 0) $maxPendapatan = 1;
                    
                    foreach($dataGrafik as $row):
                        $tinggi = ($row['pendapatan'] / $maxPendapatan) * 160;
                    ?>
                    <div style="flex:1;text-align:center;display:flex;flex-direction:column;align-items:center;">
                        <div style="width:100%;height:<?= $tinggi ?>px;background:#ffcc00;border-radius:4px 4px 0 0;min-height:10px;"></div>
                        <div style="font-size:10px;color:#64748b;margin-top:4px;">
                            <?= date('d/m', strtotime($row['tanggal'])) ?>
                        </div>
                        <div style="font-size:9px;color:#1a1a2e;font-weight:600;">
                            <?= rupiah($row['pendapatan']) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($dataGrafik)): ?>
                    <div style="width:100%;text-align:center;color:#94a3b8;padding:40px 0;">
                        <i class="fas fa-info-circle"></i> Belum ada data penjualan
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <h3><i class="fas fa-trophy"></i> Produk Terlaris</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th class="text-right">Terjual</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $produkTerlaris = getProdukTerlaris($conn, 5);
                        if ($produkTerlaris && mysqli_num_rows($produkTerlaris) > 0):
                            while($row = mysqli_fetch_assoc($produkTerlaris)):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                            <td class="text-right"><strong><?= $row['total_terjual'] ?></strong></td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
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
        
        <!-- Aksi Cepat -->
        <div style="margin-top:24px;display:flex;gap:12px;flex-wrap:wrap;">
            <a href="laporan.php" class="btn-sm btn-primary-sm" style="padding:10px 24px;font-size:14px;">
                <i class="fas fa-file-alt"></i> Lihat Laporan Lengkap
            </a>
            <a href="stok.php" class="btn-sm btn-primary-sm" style="padding:10px 24px;font-size:14px;background:#10b981;color:#fff;">
                <i class="fas fa-boxes"></i> Kelola Stok
            </a>
            <a href="menu.php" class="btn-sm btn-primary-sm" style="padding:10px 24px;font-size:14px;background:#3b82f6;color:#fff;">
                <i class="fas fa-utensils"></i> Kelola Menu
            </a>
        </div>
        
    </main>
</div>
</body>
</html>