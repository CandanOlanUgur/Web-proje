<?php
session_start();
require "db.php";

if (isset($_POST["giris_yap"])) {
    $kullanici_adi = $_POST["kullanici_adi"];
    $sifre = $_POST["sifre"];

    // Kullanıcıyı veritabanında ara
    $sorgu = $db->prepare("SELECT * FROM users WHERE username = ?");
    $sorgu->execute([$kullanici_adi]);
    $kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);

    // Kullanıcı bulundu mu, şifre eşleşti mi?
    if ($kullanici && password_verify($sifre, $kullanici["password_hash"])) {
        
        // Veritabanındaki 'rol' değerini temizleyip küçük harfe zorluyoruz (Case-sensitivity hatasını önlemek için)
        $rol = strtolower(trim($kullanici['rol']));

        // EĞER GİREN KİŞİ ADMİN VEYA SUPER_ADMIN İSE:
        // Popup girişini devre dışı bıraktığın için onları yönetici kapısına yönlendiriyoruz.
        if ($rol == 'admin' || $rol == 'super_admin') {
            // HATA DURUMU
            $_SESSION['giris_hatasi'] = "Kullanıcı adı veya şifre hatalı!";
        
            $gelinen_sayfa = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
            $gelinen_sayfa = explode('#', $gelinen_sayfa)[0]; 
        
            header("Location: " . $gelinen_sayfa . "#giris-popup");
            exit;
            
        }

        $_SESSION["kullanici_id"] = $kullanici["id"];
        $_SESSION['kullanici_adi'] = $kullanici['username'];
        
        // --- DÜZELTME 2: Veritabanında "Admin" falan yazılmışsa diye küçük harfe zorluyoruz ---
        $_SESSION['kullanici_rolu'] = strtolower(trim($kullanici['rol']));
        
        header("Location: index.php");
        exit;
    } else {
        // HATA DURUMU
        $_SESSION['giris_hatasi'] = "Kullanıcı adı veya şifre hatalı!";
        
        $gelinen_sayfa = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
        $gelinen_sayfa = explode('#', $gelinen_sayfa)[0]; 
        
        header("Location: " . $gelinen_sayfa . "#giris-popup");
        exit;
    }
}
?>