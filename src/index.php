<?php
    require 'tmdb_api.php'; // API kuryemizi çağırıyoruz

    // URL'den sayfa numarasını al, yoksa varsayılan olarak 1 kullan
    $mevcut_sayfa = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($mevcut_sayfa < 1) $mevcut_sayfa = 1;
    
    // Seçili sayfanın verilerini getir
    $populer_filmler_verisi = populerFilmleriGetir($mevcut_sayfa);

    // Vitrin için her zaman 1. sayfadan ilk 5 filmi alıyoruz (sabitlemek için ayrıca çekebilirsin ama şimdilik aynı veriyi kullanalım)
    $vitrin_filmleri = array_slice($populer_filmler_verisi['results'], 0, 5);

    // Ana akış için kalan filmleri kullanıyoruz (veya hepsini)
    $akis_filmleri = $populer_filmler_verisi['results'];

    // Toplam sayfa sayısını TMDB'den alıyoruz (Sayfalama butonları için)
    // TMDB max 500 sayfaya kadar izin verir, sınır koymak iyi olabilir.
    $toplam_sayfa = isset($populer_filmler_verisi['total_pages']) ? $populer_filmler_verisi['total_pages'] : 1;
    if ($toplam_sayfa > 500) $toplam_sayfa = 500;
?>

<?php include 'header.php'; ?>
    <div class="container">
        <main class="content">

            <!-- ... ÖNE ÇIKAN FİLMLER BÖLÜMÜ ... -->
            
            <div class="icerik-ayirici">
                
                <div class="film-akisi">
                    <div class="akis-baslik-kapsayici">
                        <h2 class="baslik">Öne çıkan Yapımlar</h2>
                        <div class="akis-filtre">
                            <span class="aktif">Tümü</span>
                            <!-- Şimdilik sadece popüler filmleri çekiyoruz, dizi kısmı için ayrı API çağrısı gerekecek -->
                        </div>     
                    </div>
                    
                    <div class="film-grid">

                        <?php foreach($akis_filmleri as $film): ?>
                            <?php
                                $resim_url = "https://image.tmdb.org/t/p/w500" . $film['poster_path'];
                                $yil = isset($film['release_date']) ? substr($film['release_date'], 0, 4) : '';
                                $puan = number_format($film['vote_average'], 1);
                                $film_adi = htmlspecialchars($film['title']);
                            ?>
                            
                            <a href="film-detay.php?id=<?php echo $film['id']; ?>" class="poster-card">
                                <img src="<?php echo $resim_url; ?>" alt="<?php echo $film_adi; ?>">

                                <span class="tur-etiketi film">FİLM</span>

                                <div class="poster-overlay">
                                    <div class="poster-bilgi">
                                        <h3 class="poster-baslik"><?php echo $film_adi; ?></h3>
                                        <div class="poster-meta">
                                            <span class="yil"><?php echo $yil; ?></span>
                                        </div>
                                    </div> 
                                    <div class="poster-puan"><?php echo $puan; ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    
                    </div>    

                    <!-- DİNAMİK SAYFALAMA (PAGINATION) -->
                    <div class="pagination">
                        <?php if($mevcut_sayfa > 1): ?>
                            <a href="?page=<?php echo $mevcut_sayfa - 1; ?>" class="page-btn prev">&laquo; Önceki</a>
                        <?php endif; ?>

                        <!-- Sayfalama numaralarını göster (örnek olarak 3 sayfa öncesi ve sonrası) -->
                        <?php
                            $baslangic = max(1, $mevcut_sayfa - 2);
                            $bitis = min($toplam_sayfa, $mevcut_sayfa + 2);

                            for($i = $baslangic; $i <= $bitis; $i++):
                        ?>
                            <a href="?page=<?php echo $i; ?>" class="page-num <?php echo ($i == $mevcut_sayfa) ? 'aktif' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        
                        <?php if($mevcut_sayfa < $toplam_sayfa): ?>
                            <a href="?page=<?php echo $mevcut_sayfa + 1; ?>" class="page-btn next">Sonraki &raquo;</a> 
                        <?php endif; ?>
                    </div>
                </div>        

                <!-- ... SIDEBAR (KATEGORİLER) KISMI BURADA ... -->
            
            </div>    

        </main>    
    </div>
    
    
<?php include 'footer.php'; ?>    