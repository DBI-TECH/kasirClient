<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config/database.php';
require_once '../../includes/fungsi.php';

// ========== PROSES HAPUS ==========
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    
    // Cek apakah barang memiliki transaksi
    $cekTransaksi = mysqli_query($conn, "SELECT COUNT(*) as total FROM detail_transaksi WHERE id_barang = $id");
    $data = mysqli_fetch_assoc($cekTransaksi);
    
    if ($data['total'] > 0) {
        // Soft delete (nonaktifkan)
        mysqli_query($conn, "UPDATE barang SET is_active = 0 WHERE id_barang = $id");
        $_SESSION['success'] = "✅ Menu berhasil dinonaktifkan (soft delete) karena sudah memiliki transaksi.";
    } else {
        // Hapus permanen
        mysqli_query($conn, "DELETE FROM barang WHERE id_barang = $id");
        $_SESSION['success'] = "✅ Menu berhasil dihapus permanen.";
    }
    header("Location: menu.php");
    exit;
}

// ========== AMBIL DATA MENU ==========
$query = "SELECT * FROM barang ORDER BY is_active DESC, tipe, id_barang";
$result = mysqli_query($conn, $query);

$itemsByTipe = [];
while ($row = mysqli_fetch_assoc($result)) {
    $tipe = $row['tipe'] ?? 'Umum';
    $itemsByTipe[$tipe][] = $row;
}

// ========== HITUNG STATISTIK ==========
$totalMenu = 0;
$totalAktif = 0;
$totalNonaktif = 0;
$totalStok = 0;
$stokHabis = 0;
$stokMenipis = 0;

foreach ($itemsByTipe as $items) {
    foreach ($items as $item) {
        $totalMenu++;
        $totalStok += (int)$item['stok'];
        
        if (isset($item['is_active']) && $item['is_active'] == 0) {
            $totalNonaktif++;
        } else {
            $totalAktif++;
        }
        
        if ((int)$item['stok'] <= 0) {
            $stokHabis++;
        } elseif ((int)$item['stok'] <= 5) {
            $stokMenipis++;
        }
    }
}
$totalKategori = count($itemsByTipe);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes" />
    <title>Kelola Menu - Admin Tuklife</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="../../assets/css/admin-style.css" />
    <style>
        .menu-stats {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }
        .stat-menu {
            background: var(--card);
            padding: 18px 20px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        .stat-menu::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary-gradient);
        }
        .stat-menu:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
        }
        .stat-menu .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-dark);
            line-height: 1.2;
        }
        .stat-menu .stat-label {
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .stat-menu .stat-value.text-green { color: #10b981; }
        .stat-menu .stat-value.text-red { color: #ef4444; }
        .stat-menu .stat-value.text-blue { color: #3b82f6; }
        .stat-menu .stat-value.text-orange { color: #f59e0b; }

        .badge-status {
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .badge-status.active {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-status.inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .stok-indicator {
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 4px;
        }
        .stok-indicator.habis { color: #ef4444; }
        .stok-indicator.menipis { color: #f59e0b; }
        .stok-indicator.aman { color: #10b981; }

        .action-group {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
        }
        .action-group .btn-action {
            font-size: 11px;
            padding: 5px 12px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 500;
        }
        .action-group .btn-edit {
            background: #eff6ff;
            color: #3b82f6;
        }
        .action-group .btn-edit:hover {
            background: #3b82f6;
            color: #fff;
        }
        .action-group .btn-delete {
            background: #fef2f2;
            color: #ef4444;
        }
        .action-group .btn-delete:hover {
            background: #ef4444;
            color: #fff;
        }
        .action-group .btn-restore {
            background: #ecfdf5;
            color: #10b981;
        }
        .action-group .btn-restore:hover {
            background: #10b981;
            color: #fff;
        }
        .action-group .btn-permanent {
            background: #fef2f2;
            color: #dc2626;
            font-weight: 600;
        }
        .action-group .btn-permanent:hover {
            background: #dc2626;
            color: #fff;
        }

        .btn-tambah {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            background: var(--primary-gradient);
            color: var(--bg-dark);
            border-radius: var(--radius-sm);
            text-decoration: none;
            font-weight: 600;
            box-shadow: var(--shadow-primary);
            transition: var(--transition);
        }
        .btn-tambah:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255,204,0,0.4);
        }

        @media (max-width: 1024px) {
            .menu-stats { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 768px) {
            .menu-stats { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .stat-menu .stat-value { font-size: 22px; }
            .stat-menu { padding: 14px 16px; }
        }
        @media (max-width: 480px) {
            .menu-stats { grid-template-columns: 1fr 1fr; gap: 8px; }
            .stat-menu .stat-value { font-size: 18px; }
            .stat-menu { padding: 10px 12px; }
            .stat-menu .stat-label { font-size: 11px; }
            .action-group .btn-action { font-size: 10px; padding: 3px 8px; }
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

    <!-- ============ OVERLAY ============ -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>

    <!-- ============ MAIN CONTENT ============ -->
    <main class="main-content">

        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
                <h1><i class="fas fa-utensils"></i> Kelola Menu</h1>
            </div>
            <div class="topbar-right">
                <span class="datetime"><i class="far fa-clock"></i> <span id="clockText"></span></span>
                <a href="tambah.php" class="btn-tambah">
                    <i class="fas fa-plus"></i> Tambah Menu
                </a>
                <a href="../../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <!-- ============ ALERT ============ -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); endif; ?>

        <!-- ============ PERINGATAN STOK ============ -->
        <?php if ($stokHabis > 0 || $stokMenipis > 0): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>⚠️ Peringatan Stok!</strong>
                <?php if ($stokHabis > 0): ?>
                    <span style="color:#ef4444;font-weight:600;"><?= $stokHabis ?> menu habis</span>
                <?php endif; ?>
                <?php if ($stokHabis > 0 && $stokMenipis > 0): ?> | <?php endif; ?>
                <?php if ($stokMenipis > 0): ?>
                    <span style="color:#f59e0b;font-weight:600;"><?= $stokMenipis ?> menu menipis (≤5)</span>
                <?php endif; ?>
                <a href="../stok.php" style="color:#92400e;font-weight:600;margin-left:8px;">Kelola Stok →</a>
            </div>
        </div>
        <?php endif; ?>

        <!-- ============ STATISTIK ============ -->
        <div class="menu-stats">
            <div class="stat-menu">
                <div class="stat-value"><?= $totalMenu ?></div>
                <div class="stat-label"><i class="fas fa-utensils"></i> Total Menu</div>
            </div>
            <div class="stat-menu">
                <div class="stat-value"><?= $totalKategori ?></div>
                <div class="stat-label"><i class="fas fa-tags"></i> Kategori</div>
            </div>
            <div class="stat-menu">
                <div class="stat-value text-green"><?= $totalAktif ?></div>
                <div class="stat-label"><i class="fas fa-check-circle"></i> Aktif</div>
            </div>
            <div class="stat-menu">
                <div class="stat-value text-red"><?= $totalNonaktif ?></div>
                <div class="stat-label"><i class="fas fa-times-circle"></i> Nonaktif</div>
            </div>
            <div class="stat-menu">
                <div class="stat-value text-blue"><?= $totalStok ?></div>
                <div class="stat-label"><i class="fas fa-boxes"></i> Total Stok</div>
            </div>
        </div>

        <!-- ============ TABEL MENU ============ -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-list"></i> Daftar Menu</h3>
                <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                    <span class="badge-count"><?= $totalMenu ?> menu</span>
                    <span class="badge badge-success"><?= $totalAktif ?> aktif</span>
                    <span class="badge badge-danger"><?= $totalNonaktif ?> nonaktif</span>
                </div>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th style="width:50px;">No</th>
                            <th style="width:120px;">Kategori</th>
                            <th>Nama Menu</th>
                            <th style="width:120px;">Harga</th>
                            <th style="width:80px;">Stok</th>
                            <th style="width:100px;">Status</th>
                            <th style="width:220px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if ($totalMenu > 0):
                            foreach ($itemsByTipe as $tipe => $items):
                                foreach ($items as $row):
                                    $isActive = !isset($row['is_active']) || $row['is_active'] == 1;
                                    $stok = (int)$row['stok'];
                                    $stokClass = $stok <= 0 ? 'habis' : ($stok <= 5 ? 'menipis' : 'aman');
                        ?>
                        <tr style="<?= !$isActive ? 'background-color: #fef2f2; opacity: 0.7;' : '' ?>">
                            <td><?= $no++ ?></td>
                            <td>
                                <span style="background:#f1f5f9;padding:2px 10px;border-radius:12px;font-size:11px;font-weight:500;color:#475569;">
                                    <?= ucfirst(htmlspecialchars($tipe)) ?>
                                </span>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($row['nama_barang']) ?></strong>
                                <?php if (!$isActive): ?>
                                    <span style="font-size:10px;color:#ef4444;margin-left:6px;">(nonaktif)</span>
                                <?php endif; ?>
                            </td>
                            <td><?= rupiah($row['harga']) ?></td>
                            <td>
                                <span class="stok-indicator <?= $stokClass ?>">
                                    <?php if ($stok <= 0): ?>
                                        <i class="fas fa-times-circle"></i> <?= $stok ?>
                                    <?php elseif ($stok <= 5): ?>
                                        <i class="fas fa-exclamation-triangle"></i> <?= $stok ?>
                                    <?php else: ?>
                                        <i class="fas fa-check-circle"></i> <?= $stok ?>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($isActive): ?>
                                    <span class="badge-status active">
                                        <i class="fas fa-check-circle"></i> Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="badge-status inactive">
                                        <i class="fas fa-times-circle"></i> Nonaktif
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-group">
                                    <?php if ($isActive): ?>
                                        <a href="edit.php?id=<?= $row['id_barang'] ?>" class="btn-action btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="?hapus=<?= $row['id_barang'] ?>" 
                                           class="btn-action btn-delete" 
                                           onclick="return confirm('⚠️ Yakin ingin menghapus menu ini?\n\nJika menu sudah memiliki transaksi, akan dinonaktifkan (soft delete).\nJika belum memiliki transaksi, akan dihapus permanen.')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    <?php else: ?>
                                        <a href="restore.php?id=<?= $row['id_barang'] ?>" 
                                           class="btn-action btn-restore" 
                                           onclick="return confirm('Yakin ingin mengaktifkan kembali menu ini?')">
                                            <i class="fas fa-undo"></i> Aktifkan
                                        </a>
                                        <a href="hapus-permanen.php?id=<?= $row['id_barang'] ?>" 
                                           class="btn-action btn-permanent" 
                                           onclick="return confirm('⚠️ PERINGATAN!\n\nAnda akan menghapus PERMANEN menu ini.\nTindakan ini TIDAK BISA DIBATALKAN!\n\nYakin ingin melanjutkan?')">
                                            <i class="fas fa-trash"></i> Hapus Permanen
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php 
                                endforeach;
                            endforeach;
                        else:
                        ?>
                        <tr>
                            <td colspan="7" style="text-align:center;padding:40px 0;color:#94a3b8;">
                                <i class="fas fa-utensils" style="font-size:36px;display:block;margin-bottom:12px;"></i>
                                Belum ada menu. <a href="tambah.php" style="color:var(--primary-dark);font-weight:600;">Tambah menu sekarang →</a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ============ KETERANGAN ============ -->
        <div style="margin-bottom:20px;padding:14px 20px;background:#f8fafc;border-radius:var(--radius-sm);border:1px solid var(--border);">
            <p style="font-size:13px;color:var(--text-secondary);margin:0;">
                <i class="fas fa-info-circle" style="color:var(--primary-dark);"></i> 
                <strong>Informasi:</strong> 
                Menu dengan status <span style="color:#ef4444;font-weight:600;">Nonaktif</span> sudah memiliki transaksi sehingga tidak bisa dihapus permanen. 
                Menu ini bisa di <span style="color:#10b981;font-weight:600;">Aktifkan</span> kembali jika diperlukan. 
                Menu yang <span style="color:#10b981;font-weight:600;">Aktif</span> dan belum memiliki transaksi akan dihapus permanen saat tombol Hapus ditekan.
            </p>
        </div>

        <!-- ============ ACTION BUTTONS ============ -->
        <div class="action-buttons">
            <a href="../dashboard.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
            <a href="tambah.php" class="btn btn-green">
                <i class="fas fa-plus"></i> Tambah Menu Baru
            </a>
            <a href="../stok.php" class="btn btn-blue">
                <i class="fas fa-boxes"></i> Kelola Stok
            </a>
        </div>

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

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
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
            if (el) {
                el.textContent = days[now.getDay()] + ', ' + d + '/' + m + '/' + y + ' ' + h + ':' + min + ':' + s;
            }
        }
        updateClock();
        setInterval(updateClock, 1000);

        // Auto close alert
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
    })();
    </script>

</body>
</html>