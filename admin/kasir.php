<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../config/database.php';
require_once '../includes/fungsi.php';

// Tambah kasir
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_kasir'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kasir']);
    
    // ==== PERBAIKAN: Cek duplikasi ====
    $cek = mysqli_query($conn, "SELECT * FROM kasir WHERE nama_kasir = '$nama'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "Kasir dengan nama '$nama' sudah ada!";
    } else {
        $query = "INSERT INTO kasir (nama_kasir) VALUES ('$nama')";
        mysqli_query($conn, $query);
        header("Location: kasir.php?success=1");
        exit;
    }
}

// Hapus kasir
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM kasir WHERE id_kasir = $id");
    header("Location: kasir.php");
    exit;
}

$query = "SELECT * FROM kasir ORDER BY id_kasir";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kasir - Admin Tuklife</title>
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
        
        .form-group {
            display: flex;
            gap: 12px;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .form-group input {
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            min-width: 200px;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #ffcc00;
        }
        
        .btn-add {
            padding: 10px 24px;
            background: #ffcc00;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            color: #1a1a2e;
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
        
        .action-link-danger {
            color: #ef4444;
            text-decoration: none;
            font-size: 13px;
        }
        .action-link-danger:hover { text-decoration: underline; }
        
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
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            border-left: 4px solid #ef4444;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        @media (max-width: 768px) {
            .sidebar { width: 200px; }
            .main-content { margin-left: 200px; padding: 16px; }
            .form-group { flex-direction: column; align-items: stretch; }
            .form-group input { min-width: auto; }
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
            <li><a href="stok.php"><i class="fas fa-boxes"></i> Kelola Stok</a></li>
            <li><a href="kasir.php" class="active"><i class="fas fa-users"></i> Kelola Kasir</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    
    <main class="main-content">
        <div class="top-bar">
            <h1><i class="fas fa-users"></i> Kelola Kasir</h1>
            <a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
        <div class="alert-success">
            <i class="fas fa-check-circle"></i> Kasir berhasil ditambahkan!
        </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
        <div class="alert-error">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        
        <!-- Tambah Kasir -->
        <div class="card">
            <h3><i class="fas fa-user-plus"></i> Tambah Kasir</h3>
            <form method="POST" class="form-group">
                <input type="text" name="nama_kasir" placeholder="Nama Kasir" required>
                <button type="submit" name="tambah_kasir" class="btn-add">
                    <i class="fas fa-plus"></i> Tambah
                </button>
            </form>
        </div>
        
        <!-- Daftar Kasir -->
        <div class="card">
            <h3><i class="fas fa-list"></i> Daftar Kasir</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kasir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if (mysqli_num_rows($result) > 0):
                        while ($row = mysqli_fetch_assoc($result)):
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['nama_kasir']) ?></td>
                        <td>
                            <a href="?hapus=<?= $row['id_kasir'] ?>" class="action-link-danger" onclick="return confirm('Yakin hapus kasir ini?')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="3" style="text-align:center;padding:30px;color:#94a3b8;">
                            <i class="fas fa-info-circle"></i> Belum ada kasir
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <a href="dashboard.php" class="btn-add" style="background:#64748b;color:white;text-decoration:none;">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        
    </main>
</div>
</body>
</html>