<?php
session_start();
include 'config/database.php';
include 'includes/fungsi.php';

$error = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $username = mysqli_real_escape_string($conn, $username);
    $role = $_POST['role'] ?? 'kasir';
    
    if ($role == 'admin') {
        if (isAdmin($conn, $username, $_POST['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['role'] = 'admin';
            header("Location: admin/dashboard.php");
            exit;
        } else {
            $error = "Username atau password admin salah!";
        }
    } else {
        // Login sebagai Kasir
        $query = mysqli_query($conn, "SELECT * FROM kasir WHERE nama_kasir='$username'");
        if (mysqli_num_rows($query) > 0) {
            $data = mysqli_fetch_assoc($query);
            $_SESSION['login'] = true;
            $_SESSION['role'] = 'kasir';
            $_SESSION['id_kasir'] = $data['id_kasir'];
            $_SESSION['nama_kasir'] = $data['nama_kasir'];
            $_SESSION['nama'] = $data['nama_kasir'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Kasir tidak ditemukan!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Kasir</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins', sans-serif;
}

body{
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background: linear-gradient(
        135deg,
        #d8c091,
        #e4c98d,
        #d9be85
    );
    padding:20px;
}

.login-container{
    width:420px;
    max-width:100%;
    background:#fff;
    padding:40px;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}

.logo{
    text-align:center;
    margin-bottom:25px;
}

.logo img{
    max-width:160px;
    width:100%;
    height:auto;
    display:block;
    margin:0 auto 15px;
    transition: all 0.3s ease;
}

.logo h1{
    color:#2c2c54;
    font-size:32px;
    font-weight:700;
}

.logo p{
    color:#777;
    font-size:14px;
}

.title{
    text-align:center;
    margin-bottom:25px;
}

.title h2{
    color:#2c2c54;
}

.form-group{
    margin-bottom:20px;
}

.form-group label{
    display:block;
    margin-bottom:8px;
    font-weight:500;
    color:#444;
}

.form-group input,
.form-group select{
    width:100%;
    padding:12px 15px;
    border:2px solid #eee;
    border-radius:10px;
    outline:none;
    transition:.3s;
    font-size:14px;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

.form-group input:focus,
.form-group select:focus{
    border-color:#e0b200;
}

.form-group select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 15px center;
    padding-right: 40px;
}

.btn-login{
    width:100%;
    border:none;
    padding:13px;
    border-radius:10px;
    background:#f2c200;
    color:#222;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
    transition:.3s;
}

.btn-login:hover{
    background:#ddb000;
    transform:translateY(-2px);
}

.btn-login:active {
    transform: translateY(0);
}

.error{
    background:#ffe0e0;
    color:#d60000;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
    text-align:center;
    font-size:14px;
}

.footer{
    text-align:center;
    margin-top:20px;
    color:#777;
    font-size:13px;
}

.role-info{
    font-size:12px;
    color:#888;
    margin-top:5px;
    text-align:center;
}

/* ========== RESPONSIVE STYLES ========== */

/* Tablet dan layar sedang */
@media screen and (max-width: 768px) {
    .login-container {
        width: 90%;
        padding: 30px 25px;
        border-radius: 15px;
    }

    .logo img {
        max-width: 130px;
    }

    .logo h1 {
        font-size: 28px;
    }

    .logo p {
        font-size: 13px;
    }

    .title h2 {
        font-size: 24px;
    }

    .form-group input,
    .form-group select {
        padding: 11px 14px;
        font-size: 14px;
    }

    .btn-login {
        padding: 12px;
        font-size: 15px;
    }
}

/* HP besar (landscape) */
@media screen and (max-width: 576px) {
    body {
        padding: 15px;
        align-items: flex-start;
        padding-top: 40px;
    }

    .login-container {
        width: 100%;
        padding: 25px 20px;
        border-radius: 12px;
        margin: 0 auto;
    }

    .logo img {
        max-width: 110px;
        margin-bottom: 10px;
    }

    .logo h1 {
        font-size: 24px;
    }

    .logo p {
        font-size: 12px;
    }

    .title {
        margin-bottom: 20px;
    }

    .title h2 {
        font-size: 20px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        font-size: 13px;
        margin-bottom: 5px;
    }

    .form-group input,
    .form-group select {
        padding: 10px 12px;
        font-size: 13px;
        border-radius: 8px;
    }

    .form-group select {
        background-position: right 12px center;
        padding-right: 35px;
    }

    .btn-login {
        padding: 11px;
        font-size: 14px;
        border-radius: 8px;
    }

    .error {
        padding: 10px;
        font-size: 13px;
        border-radius: 6px;
        margin-bottom: 12px;
    }

    .footer {
        font-size: 12px;
        margin-top: 15px;
    }

    .role-info {
        font-size: 11px;
    }
}

/* HP kecil (portrait) */
@media screen and (max-width: 380px) {
    body {
        padding: 10px;
        padding-top: 30px;
    }

    .login-container {
        padding: 20px 15px;
        border-radius: 10px;
    }

    .logo img {
        max-width: 90px;
        margin-bottom: 8px;
    }

    .logo h1 {
        font-size: 20px;
    }

    .logo p {
        font-size: 11px;
    }

    .title h2 {
        font-size: 18px;
        margin-bottom: 5px;
    }

    .form-group {
        margin-bottom: 12px;
    }

    .form-group label {
        font-size: 12px;
    }

    .form-group input,
    .form-group select {
        padding: 8px 10px;
        font-size: 12px;
        border-radius: 6px;
        border-width: 1.5px;
    }

    .btn-login {
        padding: 10px;
        font-size: 13px;
        border-radius: 6px;
    }

    .error {
        padding: 8px;
        font-size: 12px;
        border-radius: 5px;
        margin-bottom: 10px;
    }

    .footer {
        font-size: 11px;
        margin-top: 12px;
    }

    .role-info {
        font-size: 10px;
    }
}

/* Untuk layar sangat tinggi (misal: HP dengan aspect ratio tinggi) */
@media screen and (max-height: 700px) {
    body {
        padding-top: 20px;
        align-items: flex-start;
    }

    .login-container {
        padding: 20px;
    }

    .logo img {
        max-width: 100px;
        margin-bottom: 10px;
    }

    .logo h1 {
        font-size: 22px;
    }

    .logo p {
        font-size: 12px;
    }

    .title {
        margin-bottom: 15px;
    }

    .form-group {
        margin-bottom: 12px;
    }
}

/* Untuk layar sangat lebar (monitor besar) */
@media screen and (min-width: 1200px) {
    .login-container {
        width: 480px;
        padding: 50px;
        border-radius: 25px;
    }

    .logo img {
        max-width: 200px;
    }

    .logo h1 {
        font-size: 36px;
    }

    .form-group input,
    .form-group select {
        padding: 14px 18px;
        font-size: 15px;
    }

    .btn-login {
        padding: 15px;
        font-size: 17px;
    }
}

/* Dark mode support (opsional) */
@media (prefers-color-scheme: dark) {
    /* Jika ingin mendukung dark mode, bisa ditambahkan */
    /* Tapi untuk kasir, lebih baik tetap terang */
}

/* Touch-friendly untuk mobile */
@media (hover: none) {
    .btn-login:hover {
        transform: none;
        background: #f2c200;
    }

    .btn-login:active {
        background: #ddb000;
        transform: scale(0.98);
    }
}

/* Landscape mode untuk HP */
@media screen and (max-width: 768px) and (orientation: landscape) {
    body {
        padding: 15px;
        align-items: center;
    }

    .login-container {
        max-width: 500px;
        padding: 20px 30px;
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
    }

    .logo img {
        max-width: 80px;
        margin: 0;
    }

    .logo-text {
        text-align: left;
    }

    .logo h1 {
        font-size: 22px;
    }

    .logo p {
        font-size: 12px;
    }

    .form-group {
        margin-bottom: 12px;
    }

    .form-group input,
    .form-group select {
        padding: 8px 12px;
        font-size: 13px;
    }

    .btn-login {
        padding: 10px;
        font-size: 14px;
    }
}

/* Print styles */
@media print {
    body {
        background: white;
        padding: 20px;
    }
    .login-container {
        box-shadow: none;
        border: 1px solid #ddd;
    }
    .btn-login {
        display: none;
    }
}
</style>
</head>
<body>

<div class="login-container">

<div class="logo">
    <img src="assets/img/logop2.png" alt="Tuklife Logo">
    <div class="logo-text">
        <p>Sistem Kasir Coffee Shop</p>
    </div>
</div>

    <div class="title">
        <h2>Login</h2>
    </div>

    <?php if($error != '') : ?>
        <div class="error">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <div class="form-group">
            <label>Login Sebagai</label>
            <select name="role" id="role" onchange="togglePasswordField()">
                <option value="kasir">Kasir</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" placeholder="Masukkan username" required>
        </div>

        <div class="form-group" id="passwordGroup">
            <label>Password</label>
            <input type="password" name="password" placeholder="Masukkan password (khusus admin)" id="passwordInput">
            <div class="role-info">* Password hanya diperlukan untuk login sebagai Admin</div>
        </div>

        <button type="submit" name="login" class="btn-login">
            Masuk
        </button>

    </form>

    <div class="footer">
        © <?= date('Y') ?> Tuklife Coffee
    </div>

</div>

<script>
function togglePasswordField() {
    const role = document.getElementById('role').value;
    const passwordGroup = document.getElementById('passwordGroup');
    const passwordInput = document.getElementById('passwordInput');
    
    if (role === 'admin') {
        passwordGroup.style.display = 'block';
        passwordInput.required = true;
    } else {
        passwordGroup.style.display = 'block';
        passwordInput.required = false;
        passwordInput.value = '';
    }
}

// Inisialisasi
document.addEventListener('DOMContentLoaded', function() {
    togglePasswordField();
});

// Menambahkan validasi form di sisi client
document.querySelector('form').addEventListener('submit', function(e) {
    const role = document.getElementById('role').value;
    const password = document.getElementById('passwordInput').value;
    
    if (role === 'admin' && password.trim() === '') {
        e.preventDefault();
        alert('Password wajib diisi untuk login sebagai Admin!');
        document.getElementById('passwordInput').focus();
    }
});
</script>

</body>
</html>