<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

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

        <!-- ============ SECTION GRID ============ -->
        <div class="section-grid">

            <!-- ===== TRANSAKSI TERBARU ===== -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-clock"></i> Transaksi Terbaru</h3>
                    <a href="transaksi.php" class="action-link">Lihat Semua →</a>
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
            <a href="menu.php" class="btn btn-blue">
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

    })();
    </script>

</body>
</html>