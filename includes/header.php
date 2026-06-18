<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Aplikasi Kasir - Tuklife</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>assets/img/logo-sidebar.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
    background: url("<?= BASE_URL ?>assets/img/back2.png") no-repeat center center fixed;
    background-size: cover;
    min-height: 100vh;
    font-family: "Poppins", sans-serif;
}

body::before {
  content: "";
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(121, 120, 120, 0.2);
  z-index: -1;
  pointer-events: none;
}

nav {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(12px);
  border-bottom: 2px solid rgba(255, 204, 0, 0.6);
  position: sticky;
  top: 0;
  z-index: 100;
}

nav .nav-container {
  max-width: 1400px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  flex-wrap: wrap;
}

.logo img {
  height: 70px;
  width: auto;
  object-fit: contain;
}

nav .nav-links {
  display: flex;
  gap: 4px;
  flex-wrap: wrap;
}

nav a {
  color: rgba(0, 0, 0, 0.8);
  text-decoration: none;
  padding: 14px 18px;
  font-size: 0.85rem;
  font-weight: 500;
  transition: all 0.2s;
  border-radius: 8px;
}

nav a:hover {
  color: #000000;
  background: rgb(255, 204, 0);
}

nav a.active {
  color: #000000;
  font-weight: 600;
  background: rgb(255, 204, 0);
}

main {
  max-width: 1400px;
  margin: 0 auto;
  padding: 24px 20px;
}

h1 {
  font-size: 1.6rem;
  font-weight: 600;
  margin-bottom: 24px;
  color: #1a1a2e;
  text-shadow: 0 1px 2px rgba(255, 255, 255, 0.5);
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

h2 {
  font-size: 1.3rem;
  font-weight: 600;
  margin: 24px 0 16px;
  color: #1a1a2e;
  text-shadow: 0 1px 2px rgba(255, 255, 255, 0.5);
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

h3 {
  font-size: 1.1rem;
  font-weight: 600;
  margin: 16px 0 12px;
  color: #1a1a2e;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 16px;
  margin-bottom: 32px;
}

.stat-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(2px);
  padding: 20px 12px;
  border-radius: 16px;
  border: 1px solid rgba(228, 233, 242, 0.8);
  text-align: center;
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.stat-value {
  font-size: 28px;
  font-weight: 700;
  color: #ffcc00;
}

.stat-label {
  font-size: 12px;
  color: #64748b;
  margin-top: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 20px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 500;
  text-decoration: none;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.btn-primary {
  background: #ffcc00;
  color: #1a1a2e;
}

.btn-primary:hover {
  background: #e6b800;
  transform: scale(1.02);
}

.btn-success {
  background: #10b981;
  color: white;
}

.btn-success:hover {
  background: #059669;
  transform: scale(1.02);
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
  transform: scale(1.02);
}

.btn-secondary {
  background: #64748b;
  color: white;
}

.btn-secondary:hover {
  background: #475569;
  transform: scale(1.02);
}

.btn-outline {
  background: transparent;
  border: 1px solid #cbd5e1;
  color: #1a1a2e;
}

.btn-outline:hover {
  background: #f1f5f9;
}

.btn-sm {
  padding: 6px 14px;
  font-size: 12px;
}

.table-wrapper {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(2px);
  border-radius: 16px;
  border: 1px solid rgba(228, 233, 242, 0.8);
  overflow-x: auto;
  overflow-y: visible;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
  min-width: 500px;
}

th {
  text-align: left;
  padding: 14px 16px;
  background: rgba(248, 250, 252, 0.95);
  font-weight: 600;
  color: #1a1a2e;
  border-bottom: 1px solid #e4e9f2;
}

td {
  padding: 12px 16px;
  border-bottom: 1px solid #f1f5f9;
  color: #1a2a3e;
}

tbody tr:hover {
  background: #fafcff;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-size: 13px;
  font-weight: 500;
  color: #1a1a2e;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  max-width: 380px;
  padding: 12px 14px;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: #1a1a2e;
  border-radius: 10px;
  font-size: 14px;
  font-family: inherit;
  transition: all 0.2s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #ffcc00;
  box-shadow: 0 0 0 3px rgba(255, 204, 0, 0.15);
}

button {
  padding: 12px 20px;
  border: none;
  background: #ffcc00;
  color: #1a1a2e;
  font-weight: 600;
  border-radius: 10px;
  cursor: pointer;
  transition: 0.2s;
}

button:hover {
  transform: translateY(-2px);
  opacity: 0.9;
}

.page-header {
  margin-bottom: 25px;
}

.page-header h2 {
  font-size: 28px;
  margin-bottom: 10px;
  color: #1a1919;
}

.back-btn {
  display: inline-block;
  text-decoration: none;
  color: #1a1a2e;
  background: #f1f5f9;
  padding: 10px 16px;
  border-radius: 10px;
  font-size: 14px;
  transition: 0.2s;
}

.back-btn:hover {
  background: #dbd145;
  transform: translateY(-2px);
}

.page-title {
  font-size: 28px;
  margin-bottom: 10px;
  color: #1a1a2e;
}

.stok-form {
  margin-top: 25px;
}

.submit-btn {
  padding: 12px 22px;
  border: none;
  border-radius: 10px;
  background: #ffcc00;
  color: #1a1a2e;
  font-weight: 600;
  cursor: pointer;
  transition: 0.2s;
}

.submit-btn:hover {
  transform: translateY(-2px);
  opacity: 0.9;
}

.alert {
  padding: 14px 18px;
  border-radius: 12px;
  margin-bottom: 20px;
  font-size: 13px;
  background: rgba(255, 255, 255, 0.95);
  display: flex;
  align-items: center;
  gap: 10px;
}

.alert-success {
  background: rgba(236, 253, 245, 0.95);
  color: #065f46;
  border-left: 4px solid #10b981;
}

.alert-danger, .alert-error {
  background: rgba(254, 242, 242, 0.95);
  color: #991b1b;
  border-left: 4px solid #ef4444;
}

.alert-warning {
  background: rgba(255, 251, 235, 0.95);
  color: #92400e;
  border-left: 4px solid #f59e0b;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 18px;
  margin: 20px 0;
}

.product-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(2px);
  border: 1px solid rgba(228, 233, 242, 0.8);
  border-radius: 16px;
  padding: 16px;
  transition: all 0.2s;
}

.product-card:hover {
  border-color: #ffcc00;
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
  transform: translateY(-2px);
}

.category-title {
  font-weight: 600;
  font-size: 1rem;
  color: #1a1a2e;
  margin-bottom: 16px;
  padding-bottom: 10px;
  border-bottom: 3px solid #ffcc00;
  display: inline-block;
}

.product-name {
  font-weight: 600;
  color: #1a1a2e;
  margin-bottom: 8px;
  font-size: 0.95rem;
}

.product-price {
  color: #ffcc00;
  font-weight: 700;
  font-size: 1rem;
  margin-bottom: 12px;
}

.product-qty {
  width: 100%;
  padding: 10px;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: #1a1a2e;
  border-radius: 10px;
  font-size: 14px;
  text-align: center;
}

.product-subtotal {
  font-size: 11px;
  color: #10b981;
  margin-top: 8px;
  text-align: right;
  font-weight: 500;
}

.cart-summary {
  background: rgba(248, 250, 252, 0.95);
  backdrop-filter: blur(2px);
  border: 1px solid rgba(228, 233, 242, 0.8);
  border-radius: 16px;
  padding: 20px;
  margin: 24px 0;
}

.total-price {
  font-size: 22px;
  font-weight: 700;
  color: #1a1a2e;
}

.action-buttons {
  display: flex;
  gap: 12px;
  margin-top: 20px;
  flex-wrap: wrap;
}

.action-link {
  color: #ffcc00;
  text-decoration: none;
  font-size: 13px;
  margin-right: 12px;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.action-link:hover {
  text-decoration: underline;
}

.action-link-danger {
  color: #ef4444;
}

.badge {
  display: inline-flex;
  align-items: center;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.badge-success {
  background: #d1fae5;
  color: #065f46;
}

.badge-danger {
  background: #fee2e2;
  color: #991b1b;
}

.badge-warning {
  background: #fef3c7;
  color: #92400e;
}

hr {
  margin: 24px 0;
  border: none;
  border-top: 1px solid #e4e9f2;
}

.flex-between {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
}

.flex-start {
  display: flex;
  justify-content: flex-start;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.gap-2 { gap: 8px; }
.gap-3 { gap: 12px; }
.gap-4 { gap: 16px; }
.mt-2 { margin-top: 8px; }
.mt-3 { margin-top: 12px; }
.mt-4 { margin-top: 16px; }
.mb-2 { margin-bottom: 8px; }
.mb-3 { margin-bottom: 12px; }
.mb-4 { margin-bottom: 16px; }

@media (max-width: 1024px) {
  .product-grid {
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  }
}

@media (max-width: 768px) {
  nav .nav-container {
    flex-direction: column;
    padding: 10px 16px;
  }

  .logo img {
    height: 55px;
  }

  nav .nav-links {
    justify-content: center;
    margin-top: 8px;
  }

  nav a {
    padding: 8px 14px;
    font-size: 12px;
  }

  main {
    padding: 16px;
  }

  h1 {
    font-size: 1.3rem;
  }

  h2 {
    font-size: 1.1rem;
  }

  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 12px;
  }

  .stat-card {
    padding: 14px 8px;
  }

  .stat-value {
    font-size: 22px;
  }

  .stat-label {
    font-size: 10px;
  }

  .product-grid {
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 12px;
  }

  .product-card {
    padding: 12px;
  }

  .btn {
    padding: 8px 16px;
    font-size: 12px;
  }

  .action-buttons {
    gap: 8px;
  }

  th, td {
    padding: 10px 12px;
    font-size: 12px;
  }

  .form-group input,
  .form-group select {
    max-width: 100%;
    padding: 10px 12px;
  }
}

@media (max-width: 480px) {
  .product-grid {
    grid-template-columns: 1fr;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .action-buttons {
    flex-direction: column;
  }

  .btn {
    width: 100%;
    justify-content: center;
  }

  .flex-between {
    flex-direction: column;
    gap: 12px;
    align-items: flex-start;
  }

  .table-wrapper {
    border-radius: 12px;
  }

  th, td {
    padding: 8px 10px;
    font-size: 11px;
  }

  .alert {
    padding: 10px 14px;
    font-size: 12px;
  }
}

@media print {
  nav, .action-buttons, .btn, .btn-primary, .btn-secondary {
    display: none !important;
  }

  body {
    background: white;
  }

  body::before {
    display: none;
  }

  .table-wrapper {
    background: white;
    box-shadow: none;
  }
}
/* Tambahkan di bagian CSS */
.badge-danger { background: #fee2e2; color: #991b1b; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-success { background: #d1fae5; color: #065f46; }
.badge-secondary { background: #f1f5f9; color: #475569; }


    </style>
</head>
<body>
    <nav>
        <div class="nav-container">
            <div class="logo">
               <img src="<?= BASE_URL ?>assets/img/logop2.png"
     style="height:90px;width:auto;object-fit:contain;"
     alt="logo tuklife">
            </div>
            <div class="nav-links">
                <a href="<?= BASE_URL ?>index.php">Beranda</a>
                <a href="<?= BASE_URL ?>modules/stok-bahan/index.php">Stok Bahan</a>
                <a href="<?= BASE_URL ?>modules/menu/index.php">Data Menu</a>
                <a href="<?= BASE_URL ?>modules/transaksi/index.php">Transaksi</a>
                <a href="<?= BASE_URL ?>modules/kalkulator/index.php">Kalkulator Menu</a>
                <a href="<?= BASE_URL ?>logout.php">logout</a>
            </div>
        </div>
    </nav>
    <main>