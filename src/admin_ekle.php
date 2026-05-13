<?php
require "db.php";

//Form verileri
$kullanici_adi = "super_admin"; //GECICI
$eposta = "superAdmin@filmblog.com";
$sifre  = "gizliSifre123";
$rol = "admin";

//Sifreyi hashle
$hashli_sifre = password_hash($sifre, PASSWORD_DEFAULT);

$sorgu = $db -> prepare("INSERT INTO users (username, email, password_hash, rol) VALUES (?, ?, ?, ?)");
$sonuc = $sorgu -> execute([$kullanici_adi, $eposta, $hashli_sifre, $rol]);

if ($sonuc) {
    echo "SuperAdmin";
} else {
    echo "Kayit sirasinda hata meydana geldi";
}
?>