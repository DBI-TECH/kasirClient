<?php
session_start();
include 'config/database.php';

$error = '';

if (isset($_POST['login'])) {

    $username = trim($_POST['username']);

    $query = mysqli_query(
        $conn,
        "SELECT * FROM kasir WHERE nama_kasir='$username'"
    );

    if (mysqli_num_rows($query) > 0) {

        $data = mysqli_fetch_assoc($query);

        $_SESSION['login'] = true;
        $_SESSION['nama_kasir'] = $data['nama_kasir'];

        header("Location: index.php");
        exit;
    } else {
        $error = "Kasir tidak ditemukan!";
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
}

.login-container{
    width:400px;
    background:#fff;
    padding:40px;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,0.15);
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

.form-group input{
    width:100%;
    padding:12px 15px;
    border:2px solid #eee;
    border-radius:10px;
    outline:none;
    transition:.3s;
}

.form-group input:focus{
    border-color:#e0b200;
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

.error{
    background:#ffe0e0;
    color:#d60000;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
    text-align:center;
}

.footer{
    text-align:center;
    margin-top:20px;
    color:#777;
    font-size:13px;
}
</style>
</head>
<body>

<div class="login-container">

<div class="logo">
    <img src="assets/img/logop2.png" alt="Tuklife Logo">
    <p>Sistem Kasir Coffee Shop</p>
</div>

    <div class="title">
        <h2>Login Kasir</h2>
    </div>

    <?php if($error != '') : ?>
        <div class="error">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <div class="form-group">
            <label>Nama Kasir</label>
            <input type="text" name="username" placeholder="Masukkan nama kasir" required>
        </div>

        <button type="submit" name="login" class="btn-login">
            Masuk
        </button>

    </form>

    <div class="footer">
        © <?= date('Y') ?> Tuklife coffe
    </div>

</div>

</body>
</html>