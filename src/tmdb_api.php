<?php
// TMDB API Anahtarını buraya yapıştır
$tmdb_api_key = 'c3a8e7b3e56b89673fb09393c2fe8fb1';

/*
 * En popüler filmleri çeken fonksiyon
 */
function populerFilmleriGetir($sayfa = 1) {
    global $tmdb_api_key; 
    
    // 1. Hedef URL'yi oluşturuyoruz
    $url = "https://api.themoviedb.org/3/movie/popular?api_key=" . $tmdb_api_key . "&language=tr-TR&page=" . $sayfa;

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

function filmDetayGetir($id) {
    global $tmdb_api_key;
    // append_to_response=credits sayesinde tek istekte oyuncuları da alıyoruz
    $url = "https://api.themoviedb.org/3/movie/" . $id . "?api_key=" . $tmdb_api_key . "&language=tr-TR&append_to_response=credits";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $cevap = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($cevap, true);
}

function kategoriFilmleriGetir($genre_id, $sayfa=1) {
    global $tmdb_api_key;
    $url = "https://api.themoviedb.org/3/discover/movie?api_key=" . $tmdb_api_key . "&language=tr-TR&sort_by=popularity.desc&with_genres=" . $genre_id . "&page=" . $sayfa;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $cevap = curl_exec($ch);
    curl_close($ch);

    return json_decode($cevap, true);
}

function filmAra($kelime, $sayfa = 1) {
    global $tmdb_api_key;
    // urlencode() fonksiyonu boşlukları ve Türkçe karakterleri URL formatına çevirir
    $url = "https://api.themoviedb.org/3/search/movie?api_key=" . $tmdb_api_key . "&language=tr-TR&query=" . urlencode($kelime) . "&page=" . $sayfa;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $cevap = curl_exec($ch);
    curl_close($ch);

    return json_decode($cevap, true);
}



?>