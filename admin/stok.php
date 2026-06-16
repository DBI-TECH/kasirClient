<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../config/database.php';
require_once '../includes/fungsi.php';

// Proses update stok
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stok'])) {
    $id_barang = (int)$_POST['id_barang'];
    $stok_baru = (int)$_POST['stok'];
    $query = "UPDATE barang SET stok = $stok_baru WHERE id_barang = $id_barang";
    mysqli_query($conn, $query);
    header("Location: stok.php?success=1");
    exit;
}

$query = "SELECT * FROM barang ORDER BY stok ASC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Stok - Admin Tuklife</title>
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
        .badge-secondary { background: #f1f5f9; color: #475569; }
        
        .text-center { text-align: center; }
        
        .stok-form {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        
        .stok-form input[type="number"] {
            width: 80px;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 13px;
            text-align: center;
        }
        
        .stok-form input[type="number"]:focus {
            outline: none;
            border-color: #ffcc00;
        }
        
        .btn-update {
            padding: 6px 14px;
            background: #ffcc00;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 12px;
            color: #1a1a2e;
        }
        
        .btn-update:hover { background: #e6b800; }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            border-left: 4px solid #10b981;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        @media (max-width: 768px) {
            .sidebar { width: 200px; }
            .main-content { margin-left: 200px; padding: 16px; }
            .stok-form { flex-wrap: wrap; }
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
            <li><a href="menu.php"><i class="fas fa-utensils"></i> Kelola Menu</a></li>
            <li><a href="stok.php" class="active"><i class="fas fa-boxes"></i> Kelola Stok</a></li>
            <li><a href="kasir.php"><i class="fas fa-users"></i> Kelola Kasir</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    
    <main class="main-content">
        <div class="top-bar">
            <h1><i class="fas fa-boxes"></i> Kelola Stok Menu</h1>
            <a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
        <div class="alert-success">
            <i class="fas fa-check-circle"></i> Stok berhasil diperbarui!
        </div>
        <?php endif; ?>
        
        <div class="card">
            <h3><i class="fas fa-list"></i> Daftar Stok Menu</h3>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Menu</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th class="text-center">Stok</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        $totalStok = 0;
                        $stokHabis = 0;
                        while ($row = mysqli_fetch_assoc($result)):
                            $totalStok += $row['stok'];
                            if ($row['stok'] == 0) $stokHabis++;
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                            <td><?= htmlspecialchars($row['tipe'] ?? '-') ?></td>
                            <td><?= rupiah($row['harga']) ?></td>
                            <td class="text-center"><strong><?= $row['stok'] ?></strong></td>
                            <td><?= tampilkanStatusStok($row['stok']) ?></td>
                            <td>
                                <form method="POST" class="stok-form">
                                    <input type="hidden" name="id_barang" value="<?= $row['id_barang'] ?>">
                                    <input type="number" name="stok" value="<?= $row['stok'] ?>" min="0">
                                    <button type="submit" name="update_stok" class="btn-update">
                                        <i class="fas fa-save"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background:#f8fafc;font-weight:600;">
                            <td colspan="4"><strong>Total</strong></td>
                            <td class="text-center"><strong><?= $totalStok ?></strong></td>
                            <td>
                                <?php if ($stokHabis > 0): ?>
                                <span class="badge badge-danger"><?= $stokHabis ?> menu habis</span>
                                <?php else: ?>
                                <span class="badge badge-success">Semua tersedia</span>
                                <?php endif; ?>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <a href="dashboard.php" class="btn-update" style="padding:10px 24px;font-size:14px;text-decoration:none;display:inline-block;">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
        
    </main>
</div>
</body>
</html>