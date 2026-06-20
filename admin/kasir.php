<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../config/database.php';
require_once '../includes/fungsi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_kasir'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kasir']);
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes" />
    <title>Kelola Kasir - Admin Tuklife</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>assets/img/logo-sidebar.png">
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
            <li><a href="stok.php"><i class="fas fa-boxes"></i><span class="nav-label">Kelola Stok</span></a></li>
            <li><a href="kasir.php" class="active"><i class="fas fa-users"></i><span class="nav-label">Kelola Kasir</span></a></li>
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
                <h1><i class="fas fa-users"></i> Kelola Kasir</h1>
            </div>
            <div class="topbar-right">
                <span class="datetime"><i class="far fa-clock"></i> <span id="clockText"></span></span>
                <a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
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

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-user-plus"></i> Tambah Kasir</h3>
            </div>
            <form method="POST" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end;">
                <div class="form-group" style="margin-bottom:0;flex:1;min-width:200px;">
                    <label>Nama Kasir</label>
                    <input type="text" name="nama_kasir" placeholder="Masukkan nama kasir" required style="max-width:100%;">
                </div>
                <button type="submit" name="tambah_kasir" class="btn btn-primary" style="padding:10px 24px;">
                    <i class="fas fa-plus"></i> Tambah
                </button>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-list"></i> Daftar Kasir</h3>
                <span class="badge-count"><?= mysqli_num_rows($result) ?> kasir</span>
            </div>
            <div class="table-wrapper">
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
                                <a href="?hapus=<?= $row['id_kasir'] ?>" class="action-link action-link-danger" onclick="return confirm('Yakin hapus kasir ini?')">
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

        document.querySelectorAll('.alert-success, .alert-error').forEach(a => {
            setTimeout(() => {
                a.style.transition = 'opacity 0.5s ease';
                a.style.opacity = '0';
                setTimeout(() => a.parentNode && a.remove(), 500);
            }, 4000);
        });
    })();
    </script>

</body>
</html>