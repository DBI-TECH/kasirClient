<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../config/database.php';
require_once '../includes/fungsi.php';

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

$totalStok = 0;
$stokHabis = 0;
$stokMenipis = 0;
$dataStok = [];
while ($row = mysqli_fetch_assoc($result)) {
    $totalStok += $row['stok'];
    if ($row['stok'] == 0) $stokHabis++;
    elseif ($row['stok'] <= 5) $stokMenipis++;
    $dataStok[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes" />
    <title>Kelola Stok - Admin Tuklife</title>
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
            <li><a href="laporan.php"><i class="fas fa-file-alt"></i><span class="nav-label">Laporan</span></a></li>
            <li><a href="menu/menu.php"><i class="fas fa-utensils"></i><span class="nav-label">Kelola Menu</span></a></li>
            <li><a href="stok.php" class="active"><i class="fas fa-boxes"></i><span class="nav-label">Kelola Stok</span></a></li>
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
                <h1><i class="fas fa-boxes"></i> Kelola Stok Menu</h1>
            </div>
            <div class="topbar-right">
                <span class="datetime"><i class="far fa-clock"></i> <span id="clockText"></span></span>
                <a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
        <div class="alert-success">
            <i class="fas fa-check-circle"></i> Stok berhasil diperbarui!
        </div>
        <?php endif; ?>

        <div class="stats-grid" style="margin-bottom:20px;">
            <div class="stat-card">
                <div class="stat-value"><?= count($dataStok) ?></div>
                <div class="stat-label"><i class="fas fa-utensils"></i> Total Menu</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $totalStok ?></div>
                <div class="stat-label"><i class="fas fa-boxes"></i> Total Stok</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:<?= $stokHabis > 0 ? '#ef4444' : '#10b981' ?>">
                    <?= $stokHabis > 0 ? $stokHabis . ' habis' : '✅ Aman' ?>
                </div>
                <div class="stat-label"><i class="fas fa-exclamation-triangle"></i> Stok Habis</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-list"></i> Daftar Stok Menu</h3>
                <span class="badge-count"><?= count($dataStok) ?> menu</span>
            </div>
            <div class="table-wrapper">
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
                        foreach ($dataStok as $row):
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
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="action-buttons">
            <a href="dashboard.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Kembali</a>
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

        // Auto close success alert
        document.querySelectorAll('.alert-success').forEach(a => {
            setTimeout(() => {
                a.style.transition = 'opacity 0.5s ease';
                a.style.opacity = '0';
                setTimeout(() => a.parentNode && a.remove(), 500);
            }, 3000);
        });
    })();
    </script>

</body>
</html>