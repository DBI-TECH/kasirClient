<?php
// Mulai session PALING ATAS, sebelum apapun
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/database.php';
require_once '../../includes/fungsi.php';

// Ambil data barang
$itemsByTipe = ambilBarangGroupedByTipe($conn);
$itemMap = [];
foreach ($itemsByTipe as $group) {
    foreach ($group as $b) {
        $itemMap[$b['id_barang']] = $b;
    }
}

function getQtyPost(int $id_barang): int
{
    if (!isset($_POST['qty']) || !is_array($_POST['qty'])) return 0;
    if (!array_key_exists($id_barang, $_POST['qty'])) return 0;
    $q = (int)$_POST['qty'][$id_barang];
    if ($q < 0) $q = 0;
    return $q;
}

$pesan = '';
$nama_pemesan = '';

// Proses POST request (Langsung simpan transaksi)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_transaksi'])) {
    $nama_pemesan = trim($_POST['nama_pemesan'] ?? '');
    
    // Hapus titik dari input cash
    $cash_raw = $_POST['cash'] ?? '0';
    $cash_raw = str_replace('.', '', $cash_raw);
    $cash_raw = preg_replace('/[^0-9]/', '', $cash_raw);
    $cash = (int)$cash_raw;
    
    if ($nama_pemesan === '') {
        $pesan = '✗ Nama pemesan wajib diisi.';
    } else {
        // Hitung total dari form langsung
        $cartItems = [];
        $total = 0;
        $error = false;
        
        foreach ($itemsByTipe as $group) {
            foreach ($group as $item) {
                $id_barang = (int)$item['id_barang'];
                $qty = getQtyPost($id_barang);
                
                if ($qty > 0) {
                    $harga = (int)$item['harga'];
                    $total += $harga * $qty;
                    $cartItems[] = ['id_barang' => $id_barang, 'jumlah' => $qty];
                }
            }
        }
        
        if (empty($cartItems)) {
            $pesan = '✗ Tidak ada pesanan. Isi jumlah minimal 1 untuk memesan.';
            $error = true;
        }
        
        if (!$error) {
            if ($cash < $total) {
                $kurang = $total - $cash;
                $pesan = '✗ Uang pembayaran kurang ' . rupiah($kurang);
                $error = true;
            }
        }
        
        if (!$error) {
            $change = $cash - $total;
            
            mysqli_begin_transaction($conn);
            try {
                $nama_pemesan_esc = mysqli_real_escape_string($conn, $nama_pemesan);
                $query = "INSERT INTO transaksi (
                    total,
                    nama_pemesan,
                    cash,
                    `change`
                ) VALUES (
                    $total,
                    '$nama_pemesan_esc',
                    $cash,
                    $change
                )";
                
                if (!mysqli_query($conn, $query)) {
                    throw new Exception('Gagal menyimpan transaksi');
                }
                $id_transaksi = mysqli_insert_id($conn);
                
                foreach ($cartItems as $item) {
                    $q = "INSERT INTO detail_transaksi (id_transaksi, id_barang, jumlah) VALUES ($id_transaksi, {$item['id_barang']}, {$item['jumlah']})";
                    if (!mysqli_query($conn, $q)) {
                        throw new Exception('Gagal menyimpan detail transaksi');
                    }
                }
                
                mysqli_commit($conn);
                
                $_POST = [];
                
                // Redirect ke struk.php
                header("Location: struk.php?id=$id_transaksi");
                exit;
                
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $pesan = '✗ ' . $e->getMessage();
            }
        }
    }
}

// Setelah semua proses selesai, baru include header
require_once '../../includes/header.php';

// Hitung total dari POST untuk ditampilkan
$totalSementara = 0;
$orderItems = []; // Untuk menyimpan item yang dipesan (qty > 0)

foreach ($itemsByTipe as $group) {
    foreach ($group as $item) {
        $id_barang = (int)$item['id_barang'];
        $qty = getQtyPost($id_barang);
        if ($qty > 0) {
            $harga = (int)$item['harga'];
            $totalSementara += $harga * $qty;
            $orderItems[] = [
                'id' => $id_barang,
                'nama' => $item['nama_barang'],
                'harga' => $harga,
                'qty' => $qty,
                'subtotal' => $harga * $qty,
                'tipe' => $item['tipe'] ?? 'Umum'
            ];
        }
    }
}

$totalMenu = 0;
foreach ($itemsByTipe as $group) {
    $totalMenu += count($group);
}
$itemCount = count($orderItems);

// Tampilkan pesan jika ada
if (!empty($pesan)): ?>
    <div class="alert alert-<?= strpos($pesan, '✓') !== false ? 'success' : (strpos($pesan, '⚠') !== false ? 'warning' : 'error') ?>">
        <?= htmlspecialchars($pesan) ?>
    </div>
<?php endif; ?>

<style>
.product-card {
    border: 1px solid #e2e8f0;
    padding: 15px;
    border-radius: 8px;
    background: white;
    transition: all 0.3s ease;
}

.product-card:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.product-qty {
    width: 100px;
    padding: 8px;
    margin: 10px 0;
    border: 1px solid #cbd5e1;
    border-radius: 5px;
    text-align: center;
    font-size: 14px;
}

.product-qty:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59,130,246,0.1);
}

.product-subtotal {
    font-size: 12px;
    color: #10b981;
    font-weight: bold;
    margin-top: 5px;
}

.total-update {
    animation: highlight 0.5s ease;
}

@keyframes highlight {
    0% { transform: scale(1); background-color: #fbbf24; }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); background-color: transparent; }
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    color: black;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.stat-value {
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 10px;
}

.stat-label {
    font-size: 14px;
    opacity: 0.9;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
    margin-bottom: 30px;
}

.product-name {
    font-weight: bold;
    font-size: 16px;
    margin-bottom: 10px;
}

.product-price {
    color: #10b981;
    font-weight: bold;
    font-size: 18px;
    margin-bottom: 10px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-success {
    background-color: #10b981;
    color: white;
}

.btn-success:hover {
    background-color: #059669;
}

.btn-qr {
    background-color: #6b7280;
    color: white;
}

.btn-qr:hover {
    background-color: #4b5563;
}

/* Style tombol Edit seperti gambar */
.btn-edit {
    background-color: white;
    color: #3b82f6;
    border: 1px solid #3b82f6;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    margin-right: 5px;
    transition: all 0.2s;
}

.btn-edit:hover {
    background-color: #3b82f6;
    color: white;
}

/* Style tombol Hapus seperti gambar */
.btn-delete {
    background-color: white;
    color: #ef4444;
    border: 1px solid #ef4444;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.2s;
}

.btn-delete:hover {
    background-color: #ef4444;
    color: white;
}

.action-buttons {
    display: flex;
    gap: 8px;
    justify-content: flex-start;
}

.order-summary {
    background: #f8fafc;
    padding: 20px;
    border-radius: 10px;
    margin-top: 20px;
}

.order-table {
    width: 100%;
    border-collapse: collapse;
}

.order-table th,
.order-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

.order-table th {
    background: #f1f5f9;
    font-weight: bold;
}

.order-table tbody tr:hover {
    background: #fef3c7;
}

.total-final {
    font-size: 20px;
    font-weight: bold;
    text-align: right;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 2px solid #cbd5e1;
}

/* Modal dengan scroll */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    overflow-y: auto;
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 20px;
    border-radius: 10px;
    width: 380px;
    max-width: 90%;
    max-height: 85vh;
    overflow-y: auto;
    text-align: center;
}

/* Custom scrollbar untuk modal content */
.modal-content::-webkit-scrollbar {
    width: 8px;
}

.modal-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.modal-content::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.modal-content::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.modal-buttons {
    margin-top: 20px;
    display: flex;
    gap: 10px;
    justify-content: center;
}

.qr-container {
    text-align: center;
    padding: 10px;
}

.qr-container img {
    max-width: 180px;
    margin: 10px auto;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
}

.close-btn {
    background-color: #ef4444;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.close-btn:hover {
    background-color: #dc2626;
}

.btn-back-modal {
    background-color: #6c757d;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn-back-modal:hover {
    background-color: #5a6268;
}

.qr-info {
    text-align: left;
    margin: 15px 0;
    padding: 10px;
    background: #f8fafc;
    border-radius: 5px;
    font-size: 11px;
    max-height: 200px;
    overflow-y: auto;
}

.qr-info p {
    margin: 5px 0;
}

/* Scrollbar untuk qr-info */
.qr-info::-webkit-scrollbar {
    width: 6px;
}

.qr-info::-webkit-scrollbar-track {
    background: #e2e8f0;
    border-radius: 10px;
}

.qr-info::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 10px;
}

.button-group {
    display: flex;
    gap: 12px;
    margin-top: 20px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.total-box {
    background: #10b981;
    color: white;
    padding: 10px;
    border-radius: 5px;
    margin: 15px 0;
}

.total-box strong {
    display: block;
    margin-bottom: 5px;
}

.total-box span {
    font-size: 18px;
    font-weight: bold;
}

.divider {
    border-top: 1px dashed #cbd5e1;
    margin: 10px 0;
}

.row-flex {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
    font-size: 13px;
}

.button-footer {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin-top: 15px;
    position: sticky;
    bottom: 0;
    background: white;
    padding: 10px 0;
}
</style>

<h1><i class="fas fa-calculator"></i> Kalkulator Menu Kasir</h1>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= $totalMenu ?></div>
        <div class="stat-label"><i class="fas fa-list"></i> Total Menu</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" id="itemCount"><?= $itemCount ?></div>
        <div class="stat-label"><i class="fas fa-shopping-cart"></i> Item Dipesan</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" id="totalDisplay"><?= rupiah($totalSementara) ?></div>
        <div class="stat-label"><i class="fas fa-money-bill-wave"></i> Total Pesanan</div>
    </div>
</div>

<form method="POST" id="orderForm">
    <div class="form-group">
        <label for="nama_pemesan">Nama Pemesan <span style="color:red;">*</span></label>
        <input type="text" name="nama_pemesan" id="nama_pemesan" 
               value="<?= htmlspecialchars($nama_pemesan ?: ($_POST['nama_pemesan'] ?? '')) ?>" 
               placeholder="Masukkan nama pemesan" required>
    </div>
    
    <?php if (empty($itemsByTipe)): ?>
        <div class="alert alert-warning">Belum ada menu yang tersedia.</div>
    <?php else: ?>
        <?php foreach ($itemsByTipe as $tipe => $group): ?>
            <div style="margin-bottom: 30px;">
                <div class="category-title">
                    <h3><i class="fas fa-tag"></i> <?= htmlspecialchars(ucfirst($tipe)) ?></h3>
                </div>
                <div class="product-grid">
                    <?php foreach ($group as $item): 
                        $id_barang = (int)$item['id_barang'];
                        $qtyVal = $_POST['qty'][$id_barang] ?? 0;
                    ?>
                        <div class="product-card" data-price="<?= $item['harga'] ?>" data-id="<?= $id_barang ?>">
                            <div class="product-name"><?= htmlspecialchars($item['nama_barang']) ?></div>
                            <div class="product-price"><?= rupiah($item['harga']) ?></div>
                            <input type="number" name="qty[<?= $id_barang ?>]" 
                                   value="<?= $qtyVal ?>" min="0" class="product-qty"
                                   data-id="<?= $id_barang ?>">
                            <div class="product-subtotal" id="subtotal_<?= $id_barang ?>">
                                <?= $qtyVal > 0 ? '= ' . rupiah($item['harga'] * $qtyVal) : '' ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <hr>
    
    <!-- Ringkasan Pesanan Langsung -->
    <div class="order-summary" id="orderSummary" style="<?= empty($orderItems) ? 'display:none;' : '' ?>">
        <h3><i class="fas fa-receipt"></i> Ringkasan Pesanan</h3>
        <table class="order-table">
            <thead>
                <tr>
                    <th>Menu</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                    <th style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody id="orderSummaryBody">
                <?php foreach ($orderItems as $item): ?>
                <tr>
                    <td data-id="<?= $item['id'] ?>"><?= htmlspecialchars($item['nama']) ?></td>
                    <td><?= rupiah($item['harga']) ?></td>
                    <td class="qty-cell"><?= $item['qty'] ?></td>
                    <td><?= rupiah($item['subtotal']) ?></td>
                    <td class="action-buttons">
                        <button type="button" class="btn-edit" onclick="editItem(<?= $item['id'] ?>, <?= $item['qty'] ?>)">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button type="button" class="btn-delete" onclick="deleteItem(<?= $item['id'] ?>)">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="total-final">
            <strong>Total: <span id="totalFinal"><?= rupiah($totalSementara) ?></span></strong>
        </div>
    </div>
    
    <div class="form-group" style="max-width:300px;margin:15px 0;">
        <label><strong>Cash</strong></label>
        <input type="text" 
               name="cash" 
               id="cash"
               value="<?= $_POST['cash'] ?? '' ?>" 
               placeholder="Masukkan uang pembayaran"
               oninput="formatRupiahInput(this)">
    </div>
    
    <div id="kembalianInfo" style="margin: 10px 0; padding: 10px; border-radius: 5px; display: none;">
        <strong>Kembalian: </strong> <span id="kembalian">Rp 0</span>
    </div>
    
    <div class="button-group">
        <button type="submit" name="submit_transaksi" value="1" class="btn btn-success">
            <i class="fas fa-save"></i> Simpan Transaksi
        </button>
        <button type="button" class="btn btn-qr" onclick="showQRCode()">
            <i class="fas fa-qrcode"></i> QRIS Payment
        </button>
    </div>
</form>

<!-- Modal untuk Edit -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>Edit Jumlah Pesanan</h3>
        <p id="editItemName"></p>
        <input type="number" id="editQty" min="0" style="width: 100%; padding: 8px; margin: 10px 0;">
        <div class="modal-buttons">
            <button onclick="saveEdit()" class="btn-edit">Simpan</button>
            <button onclick="closeModal()" class="btn-delete">Batal</button>
        </div>
    </div>
</div>

<!-- Modal QRIS dengan scroll dan tombol tutup -->
<div id="qrModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-qrcode"></i> QRIS Payment</h3>
        <div class="qr-container">
            <!-- Gambar QRIS -->
            <img src="<?= BASE_URL ?>assets/img/qris.jpg" alt="QRIS Code" onerror="this.src='https://placehold.co/180x180?text=QRIS'">
            
            <!-- Informasi Subtotal dan Quantity -->
            <div class="row-flex">
                <span>Subtotal</span>
                <span id="qrSubtotal" style="font-weight: bold;">Rp 0</span>
            </div>
            <div class="row-flex">
                <span>Quantity</span>
                <span id="qrQuantity" style="font-weight: bold;">0</span>
            </div>
            
            <div class="divider"></div>
            
            
            
            <!-- Total Pembayaran -->
            <div class="total-box">
                <strong>Total:</strong>
                <span id="qrTotal">Rp 0</span>
            </div>
            
            <!-- Tombol Kembali dan Tutup di Bawah (sticky) -->
            <div class="button-footer">
                <button onclick="closeQRModal()" class="close-btn">
                    <i class="fas fa-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentEditId = null;

// Fungsi kembali dari modal QRIS
function goBackFromQR() {
    closeQRModal();
}

// Fungsi untuk menampilkan QRIS
function showQRCode() {
    // Hitung total pesanan dan quantity
    let total = 0;
    let quantity = 0;
    const qtyInputs = document.querySelectorAll('.product-qty');
    qtyInputs.forEach(input => {
        let qty = parseInt(input.value) || 0;
        if (qty > 0) {
            quantity += qty;
            const productCard = input.closest('.product-card');
            const price = parseInt(productCard.dataset.price);
            total += price * qty;
        }
    });
    
    if (total === 0) {
        alert('Tidak ada pesanan. Silakan tambahkan pesanan terlebih dahulu.');
        return;
    }
    
    // Update total, subtotal, dan quantity di modal QRIS
    document.getElementById('qrTotal').textContent = formatRupiah(total);
    document.getElementById('qrSubtotal').textContent = formatRupiah(total);
    document.getElementById('qrQuantity').textContent = quantity;
    
    // Tampilkan modal
    document.getElementById('qrModal').style.display = 'block';
}

function closeQRModal() {
    document.getElementById('qrModal').style.display = 'none';
}

// Tutup modal dengan tombol ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const qrModal = document.getElementById('qrModal');
        const editModal = document.getElementById('editModal');
        if (qrModal.style.display === 'block') {
            closeQRModal();
        }
        if (editModal.style.display === 'block') {
            closeModal();
        }
    }
});

function updateTotal() {
    let total = 0;
    let itemCount = 0;
    let orderItems = [];
    
    // Ambil semua input qty
    const qtyInputs = document.querySelectorAll('.product-qty');
    
    qtyInputs.forEach(input => {
        let qty = parseInt(input.value) || 0;
        if (qty > 0) {
            itemCount++;
            // Ambil harga dari data-price di parent product-card
            const productCard = input.closest('.product-card');
            const price = parseInt(productCard.dataset.price);
            const subtotal = price * qty;
            total += subtotal;
            
            // Update subtotal display
            const id = productCard.dataset.id;
            const subtotalDiv = document.getElementById(`subtotal_${id}`);
            if (subtotalDiv) {
                subtotalDiv.textContent = `= ${formatRupiah(subtotal)}`;
            }
            
            // Simpan untuk ringkasan
            const nama = productCard.querySelector('.product-name').innerText;
            orderItems.push({
                id: parseInt(id),
                nama: nama,
                harga: price,
                qty: qty,
                subtotal: subtotal
            });
        } else {
            // Reset subtotal display jika qty 0
            const productCard = input.closest('.product-card');
            const id = productCard.dataset.id;
            const subtotalDiv = document.getElementById(`subtotal_${id}`);
            if (subtotalDiv) {
                subtotalDiv.textContent = '';
            }
        }
    });
    
    // Update display total
    const totalDisplay = document.getElementById('totalDisplay');
    totalDisplay.textContent = formatRupiah(total);
    totalDisplay.classList.add('total-update');
    setTimeout(() => totalDisplay.classList.remove('total-update'), 500);
    
    // Update item count
    document.getElementById('itemCount').textContent = itemCount;
    
    // Update ringkasan pesanan
    updateOrderSummary(orderItems, total);
    
    // Update kembalian
    updateKembalian();
}

function updateOrderSummary(items, total) {
    const orderSummary = document.getElementById('orderSummary');
    const orderSummaryBody = document.getElementById('orderSummaryBody');
    const totalFinal = document.getElementById('totalFinal');
    
    if (items.length === 0) {
        orderSummary.style.display = 'none';
        return;
    }
    
    orderSummary.style.display = 'block';
    
    // Clear existing rows
    orderSummaryBody.innerHTML = '';
    
    // Add new rows with edit/delete buttons
    items.forEach(item => {
        const row = orderSummaryBody.insertRow();
        row.insertCell(0).textContent = item.nama;
        row.insertCell(1).textContent = formatRupiah(item.harga);
        row.insertCell(2).textContent = item.qty;
        row.insertCell(3).textContent = formatRupiah(item.subtotal);
        
        // Action buttons cell
        const actionCell = row.insertCell(4);
        actionCell.className = 'action-buttons';
        actionCell.innerHTML = `
            <button type="button" class="btn-edit" onclick="editItem(${item.id}, ${item.qty})">
                <i class="fas fa-edit"></i> Edit
            </button>
            <button type="button" class="btn-delete" onclick="deleteItem(${item.id})">
                <i class="fas fa-trash"></i> Hapus
            </button>
        `;
    });
    
    totalFinal.textContent = formatRupiah(total);
}

function editItem(id, currentQty) {
    currentEditId = id;
    const productCard = document.querySelector(`.product-card[data-id="${id}"]`);
    const productName = productCard.querySelector('.product-name').innerText;
    
    document.getElementById('editItemName').innerText = productName;
    document.getElementById('editQty').value = currentQty;
    document.getElementById('editModal').style.display = 'block';
}

function saveEdit() {
    const newQty = parseInt(document.getElementById('editQty').value) || 0;
    const qtyInput = document.querySelector(`.product-qty[data-id="${currentEditId}"]`);
    
    if (qtyInput) {
        qtyInput.value = newQty;
        // Trigger change event
        const event = new Event('input', { bubbles: true });
        qtyInput.dispatchEvent(event);
        updateTotal();
    }
    
    closeModal();
}

function deleteItem(id) {
    if (confirm('Apakah Anda yakin ingin menghapus item ini?')) {
        const qtyInput = document.querySelector(`.product-qty[data-id="${id}"]`);
        if (qtyInput) {
            qtyInput.value = 0;
            // Trigger change event
            const event = new Event('input', { bubbles: true });
            qtyInput.dispatchEvent(event);
            updateTotal();
        }
    }
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
    currentEditId = null;
}

function updateKembalian() {
    // Hitung total dari form
    let total = 0;
    const qtyInputs = document.querySelectorAll('.product-qty');
    qtyInputs.forEach(input => {
        let qty = parseInt(input.value) || 0;
        if (qty > 0) {
            const productCard = input.closest('.product-card');
            const price = parseInt(productCard.dataset.price);
            total += price * qty;
        }
    });
    
    // Ambil nilai cash (hilangkan titik)
    let cashInput = document.getElementById('cash');
    let cashValue = cashInput.value.replace(/\./g, '');
    let cash = parseInt(cashValue) || 0;
    
    const kembalian = cash - total;
    const kembalianInfo = document.getElementById('kembalianInfo');
    const kembalianSpan = document.getElementById('kembalian');
    
    if (cash > 0 && total > 0) {
        if (kembalian >= 0) {
            kembalianSpan.textContent = formatRupiah(kembalian);
            kembalianSpan.style.color = 'green';
            kembalianInfo.style.display = 'block';
            kembalianInfo.style.backgroundColor = '#e8f5e9';
        } else {
            kembalianSpan.textContent = formatRupiah(Math.abs(kembalian)) + ' (Kurang)';
            kembalianSpan.style.color = 'red';
            kembalianInfo.style.display = 'block';
            kembalianInfo.style.backgroundColor = '#ffebee';
        }
    } else if (total > 0 && cash === 0) {
        kembalianSpan.textContent = formatRupiah(total) + ' (Belum input cash)';
        kembalianSpan.style.color = 'orange';
        kembalianInfo.style.display = 'block';
        kembalianInfo.style.backgroundColor = '#fef3c7';
    } else {
        kembalianInfo.style.display = 'none';
    }
}

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(angka);
}

function formatRupiahInput(input) {
    // Hapus semua karakter non-digit
    let value = input.value.replace(/[^0-9]/g, '');
    
    if (value === '') {
        input.value = '';
        updateKembalian();
        return;
    }
    
    // Konversi ke integer
    let number = parseInt(value);
    
    // Format dengan titik sebagai pemisah ribuan (tampilan saja)
    let formatted = number.toLocaleString('id-ID');
    input.value = formatted;
    
    updateKembalian();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const editModal = document.getElementById('editModal');
    const qrModal = document.getElementById('qrModal');
    if (event.target == editModal) {
        closeModal();
    }
    if (event.target == qrModal) {
        closeQRModal();
    }
}

// Jalankan update total saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    updateTotal();
});

// Tambahkan event listener untuk semua input qty
document.querySelectorAll('.product-qty').forEach(input => {
    input.addEventListener('input', updateTotal);
});

// Event listener untuk cash
const cashInput = document.getElementById('cash');
if (cashInput) {
    cashInput.addEventListener('input', function() {
        updateKembalian();
    });
}
</script>

<?php include '../../includes/footer.php'; ?>