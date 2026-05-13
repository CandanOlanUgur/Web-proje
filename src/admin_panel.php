<?php 
    session_start();
    require 'db.php';

    // 1. GÜVENLİK KONTROLÜ (Yetkisiz erişimi engelle)
    if (!isset($_SESSION['kullanici_id'])) {
        header("Location: index.php");
        exit;
    }

    // Oturum açan kişinin bilgilerini ve rolünü çekiyoruz
    $rolSorgu = $db->prepare("SELECT rol, username FROM users WHERE id = ?");
    $rolSorgu->execute([$_SESSION['kullanici_id']]);
    $kullanici = $rolSorgu->fetch(PDO::FETCH_ASSOC);

    // Eğer kullanıcı yoksa veya rolü admin/super_admin DEĞİLSE ana sayfaya postala
    if (!$kullanici || ($kullanici['rol'] != 'admin' && $kullanici['rol'] != 'super_admin')) {
        header("Location: index.php");
        exit;
    }

    // 2. İSTATİSTİKLERİ ÇEKME (Hocanın istediği Widget'lar için)
    // PDO'nun fetchColumn() özelliği, sorgudan dönen tek bir değeri (sayıyı) doğrudan almamızı sağlar.
    $uyeSayisi = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $yorumSayisi = $db->query("SELECT COUNT(*) FROM comments")->fetchColumn();
    $favoriSayisi = $db->query("SELECT COUNT(*) FROM favorites")->fetchColumn();
    $puanSayisi = $db->query("SELECT COUNT(*) FROM ratings")->fetchColumn();

    include 'header.php';
?>

<div class="container content">
    <div class="admin-shell">
        
        <div class="admin-sidebar">
            <a href="admin_panel.php" class="aktif">📊 Dashboard</a>
            <a href="kullanicilar.php">👥 Kullanıcı Yönetimi</a>
            <a href="admin_profil.php">👤 Profil Ayarları</a>
            
            <?php if($kullanici['rol'] == 'super_admin'): ?>
                <a href="site_ayarlari.php">⚙️ Site Ayarları</a>
            <?php endif; ?>
        </div>

        <div class="admin-content">
            <h2 style="font-size: 28px; margin-bottom: 10px; color: #fff;">Yönetim Paneli</h2>
            <p style="color: #888; font-size: 15px;">FilmBlog yönetim paneline giriş yaptınız. Sistemin anlık istatistiklerini ve özet durumunu aşağıdan takip edebilirsiniz.</p>

            <div class="dashboard-widgets">
                <div class="widget-card">
                    <h3><?php echo $uyeSayisi; ?></h3>
                    <p>Kayıtlı Üye</p>
                </div>
                <div class="widget-card">
                    <h3><?php echo $yorumSayisi; ?></h3>
                    <p>Yapılan Yorum</p>
                </div>
                <div class="widget-card">
                    <h3><?php echo $favoriSayisi; ?></h3>
                    <p>Favoriye Eklenme</p>
                </div>
                <div class="widget-card">
                    <h3><?php echo $puanSayisi; ?></h3>
                    <p>Verilen Puan</p>
                </div>
            </div>
            
        </div>

    </div>
</div>

<?php include 'footer.php'; ?>