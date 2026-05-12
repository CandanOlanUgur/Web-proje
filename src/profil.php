<?php
    session_start();
    require 'db.php';
    require 'tmdb_api.php'; // Favori filmlerin afişlerini çekmek için kuryemizi çağırıyoruz

    // Giriş yapmamış kullanıcıları ana sayfaya yönlendir (Güvenlik)
    if (!isset($_SESSION['kullanici_id'])) {
        header("Location: index.php");
        exit;
    }

    $user_id = $_SESSION['kullanici_id'];
    $mesaj = '';
    $mesaj_tur = ''; // 'success' (yeşil) veya 'error' (kırmızı) bildirimler için

    // FORMLAR GÖNDERİLDİĞİNDE YAPILACAK İŞLEMLER
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['islem'])) {

        // 1. KULLANICI ADI VE E-POSTA GÜNCELLEME
        if ($_POST['islem'] == 'bilgi_guncelle') {
            $yeni_kullanici_adi = trim($_POST['username']);
            $yeni_email = trim($_POST['email']);
        
            if (!empty($yeni_kullanici_adi) && !empty($yeni_email)) {
                $guncelle = $db->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                if ($guncelle->execute([$yeni_kullanici_adi, $yeni_email, $user_id])) {
                    $mesaj = "Bilgileriniz başarıyla güncellendi.";
                    $mesaj_tur = "success";
                } else {
                    $mesaj = "Güncelleme sırasında bir hata oluştu.";
                    $mesaj_tur = "error";
                }
            }
        }

        // 2. PAROLA GÜNCELLEME
        elseif ($_POST['islem'] == 'sifre_guncelle') {
            $eski_sifre = $_POST['eski_sifre'];
            $yeni_sifre = $_POST['yeni_sifre'];

            // Önce kullanıcının eski şifresinin doğru olup olmadığını kontrol et
            $kontrol = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
            $kontrol->execute([$user_id]);
            $kullanici = $kontrol->fetch(PDO::FETCH_ASSOC);

            if (password_verify($eski_sifre, $kullanici['password_hash'])) {
                // Şifre doğruysa yeni şifreyi hash'le ve kaydet
                $yeni_hash = password_hash($yeni_sifre, PASSWORD_DEFAULT);
                $sifre_guncelle = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $sifre_guncelle->execute([$yeni_hash, $user_id]);
                $mesaj = "Şifreniz başarıyla değiştirildi.";
                $mesaj_tur = "success";
            } else {
                $mesaj = "Eski şifrenizi yanlış girdiniz!";
                $mesaj_tur = "error";
            }
        }

        // 3. HESAP SİLME
        elseif ($_POST['islem'] == 'hesap_sil') {
            $sil = $db->prepare("DELETE FROM users WHERE id = ?");
            if ($sil->execute([$user_id])) {
                // Veritabanındaki Foreign Key (ON DELETE CASCADE) özelliği sayesinde,
                // bu kullanıcının yaptığı tüm yorumlar ve favoriler de otomatik silinir.
                session_destroy();
                header("Location: index.php");
                exit;
            }
        }    
    }

    // SAYFA YÜKLENİRKEN KULLANICI BİLGİLERİNİ GETİR
    $kullaniciSorgu = $db->prepare("SELECT username, email FROM users WHERE id = ?");
    $kullaniciSorgu->execute([$user_id]);
    $kullanici_bilgi = $kullaniciSorgu->fetch(PDO::FETCH_ASSOC);

    // SAYFA YÜKLENİRKEN FAVORİ FİLM ID'LERİNİ GETİR
    $favSorgu = $db->prepare("SELECT movie_id FROM favorites WHERE user_id = ?");
    $favSorgu->execute([$user_id]);
    $favori_id_listesi = $favSorgu->fetchAll(PDO::FETCH_COLUMN);
?>    

<?php include 'header.php'; ?>

<div class="container content">
    <h2 class="vitrin-baslik">Profilim</h2>

    <?php if(!empty($mesaj)): ?>
        <div class="bildirim <?php echo $mesaj_tur; ?>">
            <?php echo $mesaj; ?>
        </div>
    <?php endif; ?>

    <div class="profil-layout">
        
        <div class="ayarlar-sutunu">
            
            <div class="profil-kart">
                <h3>Kişisel Bilgiler</h3>
                <form method="POST">
                    <input type="hidden" name="islem" value="bilgi_guncelle">
                    <div class="form-group">
                        <label>Kullanıcı Adı</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($kullanici_bilgi['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>E-Posta Adresi</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($kullanici_bilgi['email'] ?? ''); ?>" required>
                    </div>
                    <button type="submit" class="btn-kaydet">Bilgileri Güncelle</button>
                </form>
            </div>

            <div class="profil-kart">
                <h3>Parola Değiştir</h3>
                <form method="POST">
                    <input type="hidden" name="islem" value="sifre_guncelle">
                    <div class="form-group">
                        <label>Mevcut Parola</label>
                        <input type="password" name="eski_sifre" required>
                    </div>
                    <div class="form-group">
                        <label>Yeni Parola</label>
                        <input type="password" name="yeni_sifre" required>
                    </div>
                    <button type="submit" class="btn-kaydet">Parolayı Güncelle</button>
                </form>
            </div>

            <div class="profil-kart">
                <h3>Hesap İşlemleri</h3>
                <p style="font-size: 13px; color: #888; margin-bottom: 15px;">
                    Hesabınızı sildiğinizde; favori filmleriniz, verdiğiniz puanlar ve tüm yorumlarınız kalıcı olarak silinecektir.
                </p>
                <form method="POST" onsubmit="return confirm('Hesabınızı kalıcı olarak silmek istediğinize emin misiniz? Bu işlem geri alınamaz.');">
                    <input type="hidden" name="islem" value="hesap_sil">
                    <button type="submit" class="btn-kaydet btn-sil">Hesabımı Tamamen Sil</button>
                </form>
            </div>

        </div>

        <div class="favoriler-sutunu">
            <div class="profil-kart">
                <h3>Favori Filmlerim (<?php echo count($favori_id_listesi); ?>)</h3>
                
                <?php if (count($favori_id_listesi) > 0): ?>
                    <div class="film-grid" style="grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 15px;">
                        
                        <?php foreach($favori_id_listesi as $fav_movie_id): ?>
                            <?php 
                                // ID'yi API'ye gönderip afişini çekiyoruz
                                $film = filmDetayGetir($fav_movie_id); 
                                // API'den başarılı bir yanıt geldiyse ekrana bas
                                if(isset($film['id'])):
                                    $resim_url = "https://image.tmdb.org/t/p/w500" . $film['poster_path'];
                                    $film_adi = htmlspecialchars($film['title']);
                            ?>
                            <a href="film-detay.php?id=<?php echo $film['id']; ?>" class="poster-card" style="height: 220px;">
                                <img src="<?php echo $resim_url; ?>" alt="<?php echo $film_adi; ?>">
                                <div class="poster-overlay" style="padding: 10px;">
                                    <h3 class="poster-baslik" style="font-size: 14px; text-align: center;"><?php echo $film_adi; ?></h3>
                                </div>
                            </a>
                            <?php endif; ?>
                        <?php endforeach; ?>

                    </div>
                <?php else: ?>
                    <p style="color: #777; text-align: center; padding: 40px 0;">
                        Henüz favori listenize bir film eklemediniz. Keşfetmeye başlayın!
                    </p>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php include 'footer.php'; ?>