<?php
session_start();
require 'db.php';

// 1. GÜVENLİK KONTROLÜ (Admin yetkisi olmayan giremez) 
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['kullanici_id'];
$mesaj = '';
$mesaj_tur = '';

// Kullanıcının rolünü ve mevcut bilgilerini çekelim
$sorgu = $db->prepare("SELECT username, email, rol, password_hash FROM users WHERE id = ?");
$sorgu->execute([$user_id]);
$kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);

if (!$kullanici || ($kullanici['rol'] != 'admin' && $kullanici['rol'] != 'super_admin')) {
    header("Location: index.php");
    exit;
}

// 2. GÜNCELLEME İŞLEMLERİ (Prepared Statements kullanarak) [cite: 6, 7]
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['islem'])) {
    
    // Bilgi Güncelleme
    if ($_POST['islem'] == 'bilgi_guncelle') {
        $yeni_username = trim($_POST['username']);
        $yeni_email = trim($_POST['email']);
        
        $guncelle = $db->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        if ($guncelle->execute([$yeni_username, $yeni_email, $user_id])) {
            $mesaj = "Profil bilgileriniz başarıyla güncellendi.";
            $mesaj_tur = "success";
            // Sayfadaki bilgileri tazele
            $kullanici['username'] = $yeni_username;
            $kullanici['email'] = $yeni_email;
        }
    } 
    
    // Şifre Değiştirme [cite: 10, 11]
    elseif ($_POST['islem'] == 'sifre_guncelle') {
        $eski_sifre = $_POST['eski_sifre'];
        $yeni_sifre = $_POST['yeni_sifre'];
        
        if (password_verify($eski_sifre, $kullanici['password_hash'])) {
            $yeni_hash = password_hash($yeni_sifre, PASSWORD_DEFAULT);
            $sifre_guncelle = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $sifre_guncelle->execute([$yeni_hash, $user_id]);
            $mesaj = "Yönetici şifreniz başarıyla değiştirildi.";
            $mesaj_tur = "success";
        } else {
            $mesaj = "Mevcut şifreniz hatalı!";
            $mesaj_tur = "error";
        }
    }
}

include 'header.php';
?>

<div class="container content">
    <div class="admin-shell">
        
        <div class="admin-sidebar">
            <a href="admin_panel.php">📊 Dashboard</a>
            <a href="kullanicilar.php">👥 Kullanıcı Yönetimi</a>
            <a href="admin_profil.php" class="aktif">👤 Profil Ayarları</a>
            <?php if($kullanici['rol'] == 'super_admin'): ?>
                <a href="site_ayarlari.php">⚙️ Site Ayarları</a>
            <?php endif; ?>
        </div>

        <div class="admin-content">
            <h2 class="admin-baslik">Admin Profil Ayarları</h2>

            <?php if(!empty($mesaj)): ?>
                <div class="bildirim <?php echo $mesaj_tur; ?>">
                    <?php echo $mesaj; ?>
                </div>
            <?php endif; ?>

            <div class="profil-layout" style="grid-template-columns: 1fr;">
                
                <div class="profil-kart">
                    <h3>Yönetici Bilgileri</h3>
                    <form method="POST">
                        <input type="hidden" name="islem" value="bilgi_guncelle">
                        <div class="form-group">
                            <label>Kullanıcı Adı</label>
                            <input type="text" name="username" value="<?php echo htmlspecialchars($kullanici['username']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>E-Posta Adresi</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($kullanici['email']); ?>" required>
                        </div>
                        <button type="submit" class="btn-kaydet">Değişiklikleri Kaydet</button>
                    </form>
                </div>

                <div class="profil-kart">
                    <h3>Güvenlik Ayarları</h3>
                    <form method="POST">
                        <input type="hidden" name="islem" value="sifre_guncelle">
                        <div class="form-group">
                            <label>Mevcut Şifre</label>
                            <input type="password" name="eski_sifre" required>
                        </div>
                        <div class="form-group">
                            <label>Yeni Şifre</label>
                            <input type="password" name="yeni_sifre" required>
                        </div>
                        <button type="submit" class="btn-kaydet">Şifreyi Güncelle</button>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>

<?php include 'footer.php'; ?>