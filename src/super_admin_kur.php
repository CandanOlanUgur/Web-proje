<?php
    require "db.php";

    $kullanici_adi = "super_admin"; // Giriş yapacağın ad
    $eposta = "super@filmblog.com";
    $sifre  = "super123";           // Giriş yapacağın şifre
    $rol = "super_admin";

    $hashli_sifre = password_hash($sifre, PASSWORD_DEFAULT);

    // Zaten eklenmiş mi kontrol et
    $kontrol = $db->prepare("SELECT id FROM users WHERE username = ?");
    $kontrol->execute([$kullanici_adi]);

    if ($kontrol->rowCount() > 0) {
        echo "islem gerceklesirken bir hata meydana geldi!";
    } else {
        $sorgu = $db->prepare("INSERT INTO users (username, email, password_hash, rol) VALUES (?, ?, ?, ?)");
        if ($sorgu->execute([$kullanici_adi, $eposta, $hashli_sifre, $rol])) {
            echo "Süper Admin Kullanıcı: super_admin | Şifre: super123";
        }
    }
?>