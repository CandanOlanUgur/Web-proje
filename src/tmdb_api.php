<?php
// TMDB API Anahtarını buraya yapıştır
$tmdb_api_key = 'c3a8e7b3e56b89673fb09393c2fe8fb1';

/*
 * En popüler filmleri çeken fonksiyon
 */
function populerFilmleriGetir($sayfa = 1) {
    global $tmdb_api_key; 
    
    // 1. Hedef URL'yi oluşturuyoruz
    $url = "https://api.themoviedb.org/3/movie/popular?api_key=" . $tmdb_api_key . "&language=tr-TR&page=1" . $sayfa;

    // 2. cURL sistemini başlat
    $ch = curl_init();

    // 3. cURL ayarlarını yap
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    
    // EKLENEN KISIM: SSL Doğrulamasını kapat (Localhost için gerekli)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // 4. İsteği çalıştır ve cevabı yakala
    $cevap = curl_exec($ch);

    // EKLENEN KISIM: Eğer cURL çalışırken bir hata oluşursa bunu bize söylesin
    if(curl_errno($ch)){
        echo 'Curl hatası: ' . curl_error($ch);
    }

    // 5. İşin bitince cURL'ü kapat
    curl_close($ch);

    // 6. Gelen JSON formatını diziye çevir
    return json_decode($cevap, true);
}
?>