<?php
    require 'tmdb_api.php';
    
    $arama_kelimesi = isset($_GET['q']) ? trim($_GET['q']) : '';
    $mevcut_sayfa = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($mevcut_sayfa < 1) $mevcut_sayfa = 1;
    
    if($arama_kelimesi != '') {
        $filmler_verisi = filmAra($arama_kelimesi, $mevcut_sayfa);
        $akis_filmleri = isset($filmler_verisi['results']) ? $filmler_verisi['results'] : [];
        $toplam_sayfa = isset($filmler_verisi['total_pages']) ? $filmler_verisi['total_pages'] : 1;
    } else {
        $akis_filmleri = [];
        $toplam_sayfa = 1;
    }

    include 'header.php'; 
?>
<div class="container"><main class="content">
    <div class="akis-baslik-kapsayici" style="margin-top: 30px;">
        <h2 class="baslik" style="border-left: 5px solid #e50914; padding-left: 15px;">Arama Sonuçları: "<?php echo htmlspecialchars($arama_kelimesi); ?>"</h2>
    </div>
    
    <?php if(count($akis_filmleri) > 0): ?>
        <div class="film-grid">
            <?php foreach($akis_filmleri as $film): 
                $resim_url = $film['poster_path'] ? "https://image.tmdb.org/t/p/w500" . $film['poster_path'] : "varsayilan_resim.jpg";
            ?>
                <a href="film-detay.php?id=<?php echo $film['id']; ?>" class="poster-card">
                    <img src="<?php echo $resim_url; ?>">
                    <div class="poster-overlay">
                        <div class="poster-bilgi">
                            <h3 class="poster-baslik"><?php echo htmlspecialchars($film['title']); ?></h3>
                            <div class="poster-meta"><span><?php echo substr($film['release_date'] ?? '', 0, 4); ?></span></div>
                        </div> 
                        <div class="poster-puan"><?php echo number_format($film['vote_average'] ?? 0, 1); ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>    

        <div class="pagination">
            <?php if($mevcut_sayfa > 1): ?>
                <a href="?q=<?php echo urlencode($arama_kelimesi); ?>&page=<?php echo $mevcut_sayfa - 1; ?>" class="page-btn prev">&laquo; Önceki</a>
            <?php endif; ?>
            <?php if($mevcut_sayfa < $toplam_sayfa): ?>
                <a href="?q=<?php echo urlencode($arama_kelimesi); ?>&page=<?php echo $mevcut_sayfa + 1; ?>" class="page-btn next">Sonraki &raquo;</a> 
            <?php endif; ?>
        </div>
    <?php else: ?>
        <p style="color: #888; font-size: 18px;">Maalesef aradığınız kelimeye uygun bir film bulunamadı.</p>
    <?php endif; ?>
</main></div>
<?php include 'footer.php'; ?>