<?php
function rupiah($angka) {
    if ($angka === null || $angka === '' || !is_numeric($angka)) {
        $angka = 0;
    }
    return "Rp " . number_format((float)$angka, 0, ',', '.');
}

function barang_has_tipe_column($conn): bool {
    static $hasTipe = null;
    if ($hasTipe !== null) {
        return $hasTipe;
    }
    $result = mysqli_query($conn, "SHOW COLUMNS FROM barang LIKE 'tipe'");
    if (!$result) {
        $hasTipe = false;
        return $hasTipe;
    }
    $hasTipe = mysqli_num_rows($result) > 0;
    return $hasTipe;
}

function ambilBarangGroupedByTipe($conn, $filterTypes = null): array {
    $groups = [];
    if (barang_has_tipe_column($conn)) {
        $result = mysqli_query($conn, "SELECT id_barang, nama_barang, harga, tipe, stok FROM barang ORDER BY tipe, id_barang");
    } else {
        $result = mysqli_query($conn, "SELECT id_barang, nama_barang, harga, stok, '' AS tipe FROM barang ORDER BY id_barang");
    }

    if (!$result) {
        return $groups;
    }

    $validTypes = $filterTypes !== null ? $filterTypes : ['mocktail', 'milk base', 'coffe', 'snack'];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $tipe = trim($row['tipe']) !== '' ? trim($row['tipe']) : '';
        
        if ($tipe === '' || !in_array($tipe, $validTypes, true)) {
            continue;
        }
        
        $groups[$tipe][] = $row;
    }
    return $groups;
}

function ambilSemuaBarang($conn): array {
    $result = mysqli_query($conn, "SELECT * FROM barang");
    if (!$result) {
        return [];
    }
    $barang = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $barang[] = $row;
    }
    return $barang;
}

// ========== FUNGSI STOK ==========
function tampilkanStatusStok($stok) {
    if ($stok <= 0) {
        return '<span class="badge badge-danger"><i class="fas fa-times-circle"></i> Habis</span>';
    } elseif ($stok <= 5) {
        return '<span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> ' . $stok . '</span>';
    } elseif ($stok <= 10) {
        return '<span class="badge badge-secondary"><i class="fas fa-info-circle"></i> ' . $stok . '</span>';
    } else {
        return '<span class="badge badge-success"><i class="fas fa-check-circle"></i> ' . $stok . '</span>';
    }
}

// ========== FUNGSI ADMIN ==========
function getAdminByUsername($conn, $username) {
    $username = mysqli_real_escape_string($conn, $username);
    $query = "SELECT * FROM admin WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        return null;
    }
    return mysqli_fetch_assoc($result);
}

function isAdmin($conn, $username, $password) {
    $admin = getAdminByUsername($conn, $username);
    if ($admin) {
        // Gunakan password_verify jika password di-hash dengan password_hash
        // Atau tetap gunakan md5 jika database menggunakan md5
        return $admin['password'] === md5($password);
    }
    return false;
}

// ========== FUNGSI LAPORAN ==========
function getPendapatanHariIni($conn) {
    $query = "SELECT COALESCE(SUM(total), 0) as total FROM transaksi 
              WHERE DATE(tgl_transaksi) = CURDATE()";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        return 0;
    }
    $data = mysqli_fetch_assoc($result);
    return $data['total'] ?? 0;
}

function getPendapatanBulanIni($conn) {
    $query = "SELECT COALESCE(SUM(total), 0) as total FROM transaksi 
              WHERE MONTH(tgl_transaksi) = MONTH(CURDATE()) 
              AND YEAR(tgl_transaksi) = YEAR(CURDATE())";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        return 0;
    }
    $data = mysqli_fetch_assoc($result);
    return $data['total'] ?? 0;
}

function getPendapatanTotal($conn) {
    $query = "SELECT COALESCE(SUM(total), 0) as total FROM transaksi";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        return 0;
    }
    $data = mysqli_fetch_assoc($result);
    return $data['total'] ?? 0;
}

function getTotalTransaksiHariIni($conn) {
    $query = "SELECT COUNT(*) as total FROM transaksi 
              WHERE DATE(tgl_transaksi) = CURDATE()";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        return 0;
    }
    $data = mysqli_fetch_assoc($result);
    return $data['total'] ?? 0;
}

// ===== PERBAIKAN: getProdukTerlaris dengan GROUP BY lengkap =====
function getProdukTerlaris($conn, $limit = 5) {
    $query = "SELECT b.id_barang, b.nama_barang, b.harga, 
              SUM(dt.jumlah) as total_terjual, 
              SUM(dt.jumlah * b.harga) as total_pendapatan
              FROM detail_transaksi dt
              JOIN barang b ON b.id_barang = dt.id_barang
              GROUP BY b.id_barang, b.nama_barang, b.harga
              ORDER BY total_terjual DESC
              LIMIT $limit";
    return mysqli_query($conn, $query);
}

function getGrafikPenjualan($conn, $hari = 7) {
    $query = "SELECT 
              DATE(tgl_transaksi) as tanggal,
              COUNT(*) as jumlah_transaksi,
              COALESCE(SUM(total), 0) as pendapatan
              FROM transaksi
              WHERE tgl_transaksi >= DATE_SUB(NOW(), INTERVAL $hari DAY)
              GROUP BY DATE(tgl_transaksi)
              ORDER BY tanggal ASC";
    return mysqli_query($conn, $query);
}
?>