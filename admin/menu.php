<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../config/database.php';
require_once '../includes/fungsi.php';

$query = "SELECT * FROM barang ORDER BY tipe, id_barang";
$result = mysqli_query($conn, $query);
$itemsByTipe = [];

while ($row = mysqli_fetch_assoc($result)) {
    $tipe = $row['tipe'] ?? 'Umum';
    $itemsByTipe[$tipe][] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu - Admin Tuklife</title>
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
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-add {
            padding: 8px 20px;
            background: #ffcc00;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            color: #1a1a2e;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        
        .btn-add:hover { background: #e6b800; }
        
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
        
        .badge {
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        
        .action-link {
            color: #ffcc00;
            text-decoration: none;
            font-size: 12px;
            margin-right: 8px;
        }
        .action-link:hover { text-decoration: underline; }
        .action-link-danger { color: #ef4444; }
        
        @media (max-width: 768px) {
            .sidebar { width: 200px; }
            .main-content { margin-left: 200px; padding: 16px; }
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
            <li><a href="laporan.php"><i class="fas fa-file-alt"></i> Laporan</a></li>
            <li><a href="menu.php" class="active"><i class="fas fa-utensils"></i> Kelola Menu</a></li>
            <li><a href="stok.php"><i class="fas fa-boxes"></i> Kelola Stok</a></li>
            <li><a href="kasir.php"><i class="fas fa-users"></i> Kelola Kasir</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    
    <main class="main-content">
        <div class="top-bar">
            <h1><i class="fas fa-utensils"></i> Kelola Menu</h1>
            <div>
                <a href="../modules/menu/tambah.php" class="btn-add"><i class="fas fa-plus"></i> Tambah Menu</a>
                <a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <div class="card">
            <h3><i class="fas fa-list"></i> Daftar Menu</h3>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kategori</th>
                            <th>Nama Menu</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        foreach ($itemsByTipe as $tipe => $items):
                            foreach ($items as $row):
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= ucfirst(htmlspecialchars($tipe)) ?></td>
                            <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                            <td><?= rupiah($row['harga']) ?></td>
                            <td><?= $row['stok'] ?></td>
                            <td><?= tampilkanStatusStok($row['stok']) ?></td>
                            <td>
                                <a href="../modules/menu/edit.php?id=<?= $row['id_barang'] ?>" class="action-link">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="../modules/menu/hapus.php?id=<?= $row['id_barang'] ?>" class="action-link action-link-danger" onclick="return confirm('Yakin hapus?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endforeach;
                        endforeach; 
                        if ($no == 1):
                        ?>
                        <tr>
                            <td colspan="7" style="text-align:center;padding:30px;color:#94a3b8;">
                                <i class="fas fa-info-circle"></i> Belum ada menu
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <a href="dashboard.php" class="btn-add" style="background:#64748b;color:white;text-decoration:none;">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        
    </main>
</div>
</body>
</html>