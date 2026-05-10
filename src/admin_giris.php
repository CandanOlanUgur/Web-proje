<?php
session_start();
require 'db.php';

//Zaten admin olarkak giris yapilmissa direkt admin paneline at
if (isset($_SESSION['kullanici_rolu']) && $_SESSION['kullanici_rolu'] === 'admin') {
    header("Location: admin.php");
    exit;
}

$hata_mesaji = "";

if (isset($_POST['admin_giris'])) {
    $kullanici_adi = $_POST['kullanici_adi'];
    $sifre = $_POST['sifre'];

    $sorgu = $db -> prepare("SELECT * FROM users WHERE username = ? AND rol = 'admin'");
    $sorgu -> execute([$kullanici_adi]);
    $admin = $sorgu -> fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($sifre, $admin['password_hash'])) {
        //Admin oturumunu baslat
        $_SESSION['kullanici_id'] = $admin['id'];
        $_SESSION['kullanici_adi'] = $admin['username'];
        $_SESSION['kullanici_rolu'] = $admin['role'];

        header("Location: admin.php");
        exit;
    } else {
        $hata_mesaji = "Girmeye çalışırken hata meydana geldi, Daha sonra tekrar dene.";
    }
}
?>

<!DOCTYPE html>
<html lang = "tr">
    <head>
        <meta charset="UTF-8">
        <title>Yönetici kapısı</title>
        
        <style>
            body {
                background-color: #111;
                color: #fff;
                font-family: sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }

            .login-box {
                backgorund-color: #222;
                padding: 40px;
                border-radius: 8px;
                border-top: 3px solid #e50914;
                width: 300px;
            }

            input {
                width: 100%;
                padding: 10px;
                margin: 10px 0;
                background: #333;
                border: none;
                color: white;
                box-sizing: border-box;
            }

            button {
                width: 100%;
                padding: 10px;
                background: #e50914;
                color: white;
                border: none;
                font-weight: bold;
                cursor: pointer;
                margin-top: 10px;
            }

            .error {
                color: #e50914;
                font-size: 14px;
                margin-bottom: 10px;
            }
        </style>   
    </head>
    <body>
        <div class = "login-box">
            <h3>Konsol</h3>
            <?php if ($hata_mesaji) echo "<div class = 'error'>$hata_mesaji</div>"; ?>
            <form method="POST">
                <input type = "text" name = "kullanici_adi" placeholder = "Admin ID" required>
                <input type = "password" name = "sifre" placeholder = "Parola" required>
                <button type = "submit" name = "admin_giris">Bağlan</button>
            </form>
        </div>
    </body>
</html>            