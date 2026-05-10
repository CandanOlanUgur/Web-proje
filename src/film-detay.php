<?php
    session_start();
    require 'db.php';
    require 'tmdb_api.php';

    // 1. URL'den film ID'sini al
    $movie_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($movie_id == 0) { header("Location: index.php"); exit; }

    // 2. API'den film detaylarını çek
    $film = filmDetayGetir($movie_id);

    // 3. Veritabanından yorumları çek (Admin yorumları en üstte olacak şekilde)
    $yorumSorgu = $db->prepare("SELECT c.*, u.username, u.rol FROM comments c 
                                JOIN users u ON c.user_id = u.id 
                                WHERE c.movie_id = ? 
                                ORDER BY (CASE WHEN u.rol = 'admin' THEN 1 ELSE 2 END), c.created_at DESC");

    $yorumSorgu->execute([$movie_id]);
    $yorumlar = $yorumSorgu->fetchAll(PDO::FETCH_ASSOC);

    // 4. Filmin ortalama puanını bizim veritabanımızdan hesapla
    $puanSorgu = $db->prepare("SELECT AVG(rating_value) as ortalama FROM ratings WHERE movie_id = ?");
    $puanSorgu->execute([$movie_id]);
    $bizim_puan = $puanSorgu->fetch(PDO::FETCH_ASSOC)['ortalama'];
    $bizim_puan = $bizim_puan ? number_format($bizim_puan, 1) : "Henüz yok";

    $favoride_mi = false;
    if (isset($_SESSION['kullanici_id'])) {
        $fav_kontrol = $db->prepare("SELECT id FROM favorites WHERE user_id = ? AND movie_id = ?");
        $fav_kontrol->execute([$_SESSION['kullanici_id'], $movie_id]);
        if ($fav_kontrol->rowCount() > 0) {
            $favoride_mi = true;
        }
    }

    include 'header.php';
?>

<div class="film-backdrop" style="background-image: url(https://image.tmdb.org/t/p/original<?php echo $film['backdrop_path']; ?>);">
    <div class="backdrop-overlay"></div>
</div>

<div class="container detay-container">
    <div class="film-ust-bilgi">
        <div class="detay-poster-alani">
            <img src="https://image.tmdb.org/t/p/w500<?php echo $film['poster_path']; ?>" class="ana-poster">
        </div>

        <div class="detay-metin-alani">
            <h1 class="film-baslik"><?php echo htmlspecialchars($film['title']); ?> <span class="yil-etiketi">(<?php echo substr($film['release_date'], 0, 4); ?>)</span></h1>
            
            <div class="film-meta-satiri">
                <span class="sure"><?php echo $film['runtime']; ?> dk</span>
                <span class="turler">
                    <?php foreach($film['genres'] as $genre) echo $genre['name'] . " "; ?>
                </span>
            </div>

            <div class="puan-kutusu">
                <div class="puan-item">
                    <span class="sembol sari">★</span><span class="puan-degeri"><?php echo number_format($film['vote_average'], 1); ?></span>
                    <span class="puan-kaynak">TMDB</span>
                </div>
                <div class="puan-item">
                    <span class="sembol kirmizi">♥</span><span class="puan-degeri"><?php echo $bizim_puan; ?></span>
                    <span class="puan-kaynak">Üye Puanı</span>
                </div>
            </div>

            <div class="film-ozeti">
                <h3>Özet</h3>
                <p><?php echo htmlspecialchars($film['overview']); ?></p>
            </div>

            <div class="aksiyon-butonlari">
                <a href="#" class="btn-izle">▶ Fragman</a>
    
                <?php if(isset($_SESSION['kullanici_id'])): ?>
                    <form action="favori_islem.php" method="POST" style="display:inline;">
                        <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">
            
                        <?php if($favoride_mi): ?>
                            <button type="submit" class="btn-liste" style="background-color: #e50914; color: white;">
                                - Favorilerimden Çıkar
                            </button>
                        <?php else: ?>
                            <button type="submit" class="btn-liste">
                                + Favorilerime Ekle
                            </button>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if(isset($_SESSION['kullanici_id'])): ?>
    <section class="etkileşim-alani" style="margin-bottom: 40px; background: #1a1a1a; padding: 25px; border-radius: 12px;">
        <h2 class="bolum-basligi">Puan Ver ve Yorum Yap</h2>
        <?php if(isset($_SESSION['etkilesim_mesaj'])): ?>
            <div style="background-color: #ffd000; color: white; padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; font-weight: bold;">
                <?php 
                    echo $_SESSION['etkilesim_mesaj']; 
                    unset($_SESSION['etkilesim_mesaj']); // Mesajı gösterdikten sonra sil
                ?>
            </div>
        <?php endif; ?>
        <form action="etkilesim_kaydet.php" method="POST">
            <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">
            
            <div style="margin-bottom: 15px;">
                <label>Puanın (1-10):</label>
                <input type="number" name="puan" min="1" max="10" step="1" required style="width: 60px; padding: 5px;">
            </div>

            <textarea name="yorum" placeholder="Düşüncelerini paylaş..." required style="width: 100%; height: 100px; padding: 10px; background: #000; color: #fff; border: 1px solid #333; border-radius: 8px;"></textarea>
            
            <button type="submit" class="btn-kayit" style="margin-top: 10px; border:none; cursor:pointer; padding: 10px 20px;">Gönder</button>
        </form>
    </section>
    <?php else: ?>
        <p style="color: #888; margin-bottom: 40px;">Yorum yapmak ve puan vermek için lütfen giriş yapın.</p>
    <?php endif; ?>

    <section class="yorumlar-bolumu">
        <h2 class="bolum-basligi">Kullanıcı Yorumları (<?php echo count($yorumlar); ?>)</h2>
        <div class="yorum-listesi">
            <?php foreach($yorumlar as $y): ?>
                <div class="yorum-item" style="<?php echo ($y['rol'] == 'admin') ? 'border-left: 4px solid #e50914;' : ''; ?>">
                    <div class="yorum-baslik">
                        <span class="kullanici-adi">
                            <?php echo htmlspecialchars($y['username']); ?> 
                            <?php if($y['rol'] == 'admin') echo "<small style='color:#e50914;'>(Yönetici)</small>"; ?>
                        </span>
                        <span class="yorum-tarih"><?php echo date("d.m.Y H:i", strtotime($y['created_at'])); ?></span>
                    </div>
                    <p class="yorum-icerik"><?php echo nl2br(htmlspecialchars($y['comment_text'])); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?php include 'footer.php'; ?>