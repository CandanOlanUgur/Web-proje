<?php
$host = "localhost";
$dbname = "film_blog_db";
$username = "root";
$password = "";

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    //Hata modu exception
    $db -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}   catch(PDOException $e) {
    // 1. ZAMAN VE IP BİLGİSİNİ AL
    date_default_timezone_set('Europe/Istanbul'); // Saat dilimini Türkiye'ye ayarla
    $tarih_saat = date('Y-m-d H:i:s');
    $ip_adresi = $_SERVER['REMOTE_ADDR']; // Kullanıcının IP adresi
    
    // 2. HATAYI LOG DOSYASINA YAZ (Sadece yöneticinin görebileceği arka plan dosyası)
    $hata_mesaji = $e->getMessage();
    $log_metni = "[$tarih_saat] - IP: $ip_adresi - HATA: $hata_mesaji" . PHP_EOL;
    
    // FILE_APPEND ile her yeni hatayı dosyanın sonuna alt alta ekletiyoruz
    file_put_contents('sistem_hata_loglari.txt', $log_metni, FILE_APPEND);

    // 3. KULLANICIYA TEKNİK DETAY GÖSTERMEDEN KİBARCA BİLGİ VER VE SİTEYİ DURDUR
    die("Sistemde geçici bir teknik arıza meydana geldi. Lütfen daha sonra tekrar deneyin.");
}
?>