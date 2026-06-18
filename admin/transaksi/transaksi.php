<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../../config/database.php';
require_once '../../includes/fungsi.php';

$query = "SELECT t.*, k.nama_kasir 
          FROM transaksi t
          LEFT JOIN kasir k ON k.id_kasir = t.id_kasir
          ORDER BY t.id_transaksi DESC";
$result = mysqli_query($conn, $query);

$totalTransaksi = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes" />
    <title>Semua Transaksi - Admin Tuklife</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="../../assets/css/admin-style.css" />
</head>
<body>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../../assets/img/logo-sidebar.png" alt="Tuklife" class="logo-img" />
            <div class="brand">Tuklife <span>Admin</span></div>
        </div>
        <ul class="sidebar-nav">
            <li><a href="../dashboard.php"><i class="fas fa-chart-pie"></i><span class="nav-label">Dashboard</span></a></li>
            <li><a href="transaksi.php" class="active"><i class="fas fa-receipt"></i><span class="nav-label">Semua Transaksi</span></a></li>
            <li><a href="../laporan.php"><i class="fas fa-file-alt"></i><span class="nav-label">Laporan</span></a></li>
            <li><a href="../menu/menu.php"><i class="fas fa-utensils"></i><span class="nav-label">Kelola Menu</span></a></li>
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
                <h1><i class="fas fa-receipt"></i> Semua Transaksi</h1>
            </div>
            <div class="topbar-right">
                <span class="datetime"><i class="far fa-clock"></i> <span id="clockText"></span></span>
                <a href="../../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-list"></i> Daftar Transaksi</h3>
                <span class="badge-count">Total: <?= $totalTransaksi ?></span>
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
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if ($totalTransaksi > 0):
                            while ($row = mysqli_fetch_assoc($result)):
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['tgl_transaksi'])) ?></td>
                            <td><?= htmlspecialchars($row['nama_pemesan'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['nama_kasir'] ?? '-') ?></td>
                            <td class="text-right"><strong><?= rupiah($row['total']) ?></strong></td>
                            <td class="text-right"><?= rupiah($row['cash']) ?></td>
                            <td class="text-right"><?= rupiah($row['change']) ?></td>
                            <td>
                                <a href="detail.php?id=<?= $row['id_transaksi'] ?>" class="action-link">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                                <a href="hapus-transaksi.php?id=<?= $row['id_transaksi'] ?>" class="action-link action-link-danger" onclick="return confirm('Yakin hapus transaksi ini?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="8" style="text-align:center;padding:30px;color:#94a3b8;">
                                <i class="fas fa-info-circle"></i> Belum ada transaksi
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="action-buttons">
            <a href="dashboard.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Kembali</a>
            <a href="laporan.php" class="btn btn-green"><i class="fas fa-file-alt"></i> Lihat Laporan</a>
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