<?php
function rupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

function barang_has_tipe_column($conn): bool {
    static $hasTipe = null;
    if ($hasTipe !== null) {
        return $hasTipe;
    }
    $result = mysqli_query($conn, "SHOW COLUMNS FROM barang LIKE 'tipe'");
    $hasTipe = $result && mysqli_num_rows($result) > 0;
    return $hasTipe;
}

function ambilBarangGroupedByTipe($conn, $filterTypes = null): array {
    $groups = [];
    if (barang_has_tipe_column($conn)) {
        $result = mysqli_query($conn, "SELECT id_barang, nama_barang, harga, tipe FROM barang ORDER BY tipe, id_barang");
    } else {
        $result = mysqli_query($conn, "SELECT id_barang, nama_barang, harga, '' AS tipe FROM barang ORDER BY id_barang");
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
    $barang = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $barang[] = $row;
    }
    return $barang;
}
?>