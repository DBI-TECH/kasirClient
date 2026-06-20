<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/database.php';
require_once '../../includes/fungsi.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: menu.php');
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM barang WHERE id_barang = $id");
$item = mysqli_fetch_assoc($result);
if (!$item) {
    header('Location: menu.php');
    exit;
}

$availableTypes = ['mocktail', 'milk base', 'coffe', 'snack', 'lainnya'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_menu']);
    $harga = (int)$_POST['harga'];
    $stok = (int)$_POST['stok'];
    
    $query = "UPDATE barang SET 
              nama_barang='$nama', 
              harga=$harga, 
              stok=$stok";
    
    if (barang_has_tipe_column($conn)) {
        $tipeInput = trim($_POST['tipe'] ?? 'lainnya');
        $tipe = in_array($tipeInput, $availableTypes, true) ? $tipeInput : 'lainnya';
        $query .= ", tipe='$tipe'";
    }
    
    $query .= " WHERE id_barang=$id";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "✅ Menu '{$nama}' berhasil diupdate!";
    } else {
        $_SESSION['error'] = "❌ Gagal mengupdate menu: " . mysqli_error($conn);
    }
    header('Location: menu.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes" />
    <title>Edit Menu - Admin Tuklife</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>assets/img/logo-sidebar.png">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="../../assets/css/admin-style.css" />
    <style>
        .form-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .form-container .form-group {
            margin-bottom: 20px;
        }
        .form-container .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: var(--text);
        }
        .form-container .form-group input,
        .form-container .form-group select {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-xs);
            font-size: 14px;
            font-family: inherit;
            transition: var(--transition);
            background: #fff;
        }
        .form-container .form-group input:focus,
        .form-container .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(255,204,0,0.15);
        }
        .form-container .btn-submit {
            padding: 12px 30px;
            background: var(--primary-gradient);
            color: var(--bg-dark);
            border: none;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .form-container .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-primary);
        }
        .form-container .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #f1f5f9;
            color: var(--text);
            border-radius: var(--radius-sm);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }
        .form-container .btn-back:hover {
            background: #e2e8f0;
        }
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            flex-wrap: wrap;
        }
        .status-info {
            padding: 12px 16px;
            border-radius: var(--radius-xs);
            margin-bottom: 20px;
            font-size: 13px;
        }
        .status-info.active {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        .status-info.inactive {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
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
            <li><a href="../transaksi/transaksi.php"><i class="fas fa-receipt"></i><span class="nav-label">Semua Transaksi</span></a></li>
            <li><a href="../laporan.php"><i class="fas fa-file-alt"></i><span class="nav-label">Laporan</span></a></li>
            <li><a href="menu.php" class="active"><i class="fas fa-utensils"></i><span class="nav-label">Kelola Menu</span></a></li>
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

    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>

    <main class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
                <h1><i class="fas fa-edit"></i> Edit Menu</h1>
            </div>
            <div class="topbar-right">
                <span class="datetime"><i class="far fa-clock"></i> <span id="clockText"></span></span>
                <a href="../../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); endif; ?>

        <div class="card form-container">
            <div class="card-header">
                <h3><i class="fas fa-utensils"></i> Form Edit Menu</h3>
            </div>

            <?php 
            $isActive = !isset($item['is_active']) || $item['is_active'] == 1;
            ?>
            <div class="status-info <?= $isActive ? 'active' : 'inactive' ?>">
                <i class="fas fa-<?= $isActive ? 'check-circle' : 'times-circle' ?>"></i>
                Status: <strong><?= $isActive ? 'Aktif' : 'Nonaktif' ?></strong>
                <?php if (!$isActive): ?>
                    <span style="display:block;margin-top:4px;font-size:12px;">
                        Menu ini sudah memiliki transaksi. Untuk mengaktifkan kembali, gunakan tombol "Aktifkan" di halaman daftar menu.
                    </span>
                <?php endif; ?>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label>Nama Menu <span style="color:red;">*</span></label>
                    <input type="text" name="nama_menu" value="<?= htmlspecialchars($item['nama_barang']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Harga <span style="color:red;">*</span></label>
                    <input type="number" name="harga" value="<?= htmlspecialchars($item['harga']) ?>" min="0" required>
                </div>
                <div class="form-group">
                    <label>Stok</label>
                    <input type="number" name="stok" value="<?= htmlspecialchars($item['stok'] ?? 0) ?>" min="0">
                </div>
                <?php if (barang_has_tipe_column($conn)): ?>
                <div class="form-group">
                    <label>Kategori <span style="color:red;">*</span></label>
                    <select name="tipe" required>
                        <?php foreach ($availableTypes as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>" 
                                <?= isset($item['tipe']) && $item['tipe'] == $type ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucwords($type)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Update Menu
                    </button>
                    <a href="menu.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </main>

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
    })();
    </script>

</body>
</html>