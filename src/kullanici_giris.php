<?php
session_start();
require "db.php";

if (isset($_POST["giris_yap"])) {
    $kullanici_adi = $_POST["kullanici_adi"];
    $sifre = $_POST["sifre"];

    //kullaniciyi veritabanında ara
    $sorgu = $db -> prepare("SELECT * FROM users WHERE username = ?");
    $sorgu -> execute([$kullanici_adi]);
    $kullanici = $sorgu -> fetch(PDO::FETCH_ASSOC);

    //Kullanici bulundumu sifre eslestimi
    if ($kullanici && password_verify($sifre, $kullanici["password_hash"])) {
        
        if ($kullanici["rol"] === "admin") {
            header("Location: admin_giris.php");
            exit; // Yönlendirmeden sonra kodun devam etmesini engellemek zorundayız!
        }

        $_SESSION["kullanici_id"] = $kullanici["id"];
        $_SESSION['kullanici_adi'] = $kullanici['username'];
        $_SESSION['kullanici_rolu'] = $kullanici['rol'];
        
        header("Location: index.php");
        exit;
    } else {
        // HATA DURUMU: Mesajı session'a kaydet
        $_SESSION['giris_hatasi'] = "Kullanıcı adı veya şifre hatalı!";
        
        // Kullanıcıyı geldiği sayfaya (#giris-popup hedefiyle) geri gönder
        // Böylece tarayıcı sayfa yüklenince direkt pop-up'ı açık gösterecek
        $gelinen_sayfa = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
        $gelinen_sayfa = explode('#', $gelinen_sayfa)[0]; // Varsa eski # etiketini temizle
        
        header("Location: " . $gelinen_sayfa . "#giris-popup");
        exit;
    }
}
?>