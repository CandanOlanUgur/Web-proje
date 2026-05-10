<?php
session_start();
require 'db.php';

// Sadece formdan gelen kayit ol bilgisi varsa çalış
if (isset($_POST['kayit_ol'])) {

    // Form verisini temizlemek için
    $eposta = trim($_POST['eposta']);
    $kullanici_adi = trim($_POST['kullanici_adi']);
    $sifre = $_POST['sifre'];

    // 1. KONTROL: E-posta ve Kullanıcı adı sorguları
    $ePostaKontrolSorgusu = $db->prepare("SELECT id FROM users WHERE email = ?");
    $ePostaKontrolSorgusu->execute([$eposta]);

    $kullaniciAdiKontrolSorgusu = $db->prepare("SELECT id FROM users WHERE username = ?");
    $kullaniciAdiKontrolSorgusu->execute([$kullanici_adi]);
   
    $hata_mesaji = "";

    // Hata durumlarını belirleme
    if ($ePostaKontrolSorgusu->rowCount() > 0 && $kullaniciAdiKontrolSorgusu->rowCount() > 0) {
        $hata_mesaji = "Zaten bir hesabınız var gibi görünüyor. Bunun yerine giriş yapmayı deneyin.";
    } else if ($ePostaKontrolSorgusu->rowCount() > 0) {
        $hata_mesaji = "Bu e-posta zaten kullanılıyor.";
    } else if ($kullaniciAdiKontrolSorgusu->rowCount() > 0) {
        $hata_mesaji = "Bu isim zaten kullanılıyor, başka bir isim kullanmayı deneyin.";
    }

    // Eğer bir hata mesajı oluştuysa işlemi durdur ve pop-up'a geri gönder
    if ($hata_mesaji !== "") {
        $_SESSION['kayit_hatasi'] = $hata_mesaji;
        
        $gelinen_sayfa = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
        $gelinen_sayfa = explode('#', $gelinen_sayfa)[0]; 
        
        header("Location: " . $gelinen_sayfa . "#kayit-popup");
        exit;
    } else {
        // Hata yoksa kayıt işlemine geç
        $hashS = password_hash($sifre, PASSWORD_DEFAULT);
        $rol = 'user';
        
        // 'rol' sütun adı 'role' olarak düzeltildi
        $ekle_sorgusu = $db->prepare("INSERT INTO users (username, email, password_hash, rol) VALUES (?, ?, ?, ?)");
        $sonuc = $ekle_sorgusu->execute([$kullanici_adi, $eposta, $hashS, $rol]);

        if ($sonuc) {
            // lastInsertId metodu düzeltildi
            $yeni_kullanici_id = $db->lastInsertId();
            $_SESSION['kullanici_id'] = $yeni_kullanici_id;
            $_SESSION['kullanici_adi'] = $kullanici_adi;
            $_SESSION['kullanici_rolu'] = $rol;

            header("Location: index.php");
            exit;
        } else {
            // Veritabanı kayıt hatası olursa
            $_SESSION['kayit_hatasi'] = "Sistemsel bir hatadan dolayı kayıt olunamadı. Lütfen daha sonra tekrar deneyin.";
            $gelinen_sayfa = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
            $gelinen_sayfa = explode('#', $gelinen_sayfa)[0];
            header("Location: " . $gelinen_sayfa . "#kayit-popup");
            exit;
        }
    }
} else {
    header("Location: index.php");
    exit;
}
?>