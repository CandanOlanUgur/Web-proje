<?php
session_start();
require 'db.php';

// 1. SÜPER ADMİN GÜVENLİK KONTROLÜ
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: index.php"); exit;
}
$rolSorgu = $db->prepare("SELECT rol FROM users WHERE id = ?");
$rolSorgu->execute([$_SESSION['kullanici_id']]);
$kullanici_rol = $rolSorgu->fetchColumn();

if ($kullanici_rol != 'super_admin') {
    // Normal admin girmeye çalışırsa kendi paneline geri yolla
    header("Location: admin_panel.php"); exit; 
}

$mesaj = '';
$mesaj_tur = '';

// 2. FORM İŞLEMLERİ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['islem'])) {
    
    // A. SİTE AYARLARINI GÜNCELLE
    if ($_POST['islem'] == 'site_guncelle') {
        $site_title = trim($_POST['site_title']);
        $footer_about = trim($_POST['footer_about']);
        $instagram_link = trim($_POST['instagram_link']);
        $twitter_link = trim($_POST['twitter_link']);
        
        $guncelle = $db->prepare("UPDATE site_settings SET site_title = ?, footer_about = ?, instagram_link = ?, twitter_link = ? WHERE id = 1");
        if ($guncelle->execute([$site_title, $footer_about, $instagram_link, $twitter_link])) {
            $mesaj = "Site ayarları başarıyla güncellendi.";
            $mesaj_tur = "success";
        }
    }

    // B. YENİ ADMİN EKLE
    elseif ($_POST['islem'] == 'admin_ekle') {
        $yeni_kullanici = trim($_POST['username']);
        $yeni_eposta = trim($_POST['email']);
        $yeni_sifre = $_POST['sifre'];
        $rol = 'admin';

        $kontrol = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $kontrol->execute([$yeni_kullanici, $yeni_eposta]);

        if ($kontrol->rowCount() > 0) {
            $mesaj = "Bu kullanıcı adı veya e-posta zaten kullanımda!";
            $mesaj_tur = "error";
        } else {
            $hash = password_hash($yeni_sifre, PASSWORD_DEFAULT);
            $ekle = $db->prepare("INSERT INTO users (username, email, password_hash, rol) VALUES (?, ?, ?, ?)");
            if ($ekle->execute([$yeni_kullanici, $yeni_eposta, $hash, $rol])) {
                $mesaj = "Yeni yönetici (Admin) başarıyla eklendi.";
                $mesaj_tur = "success";
            }
        }
    }

    // C. ADMİN SİL (Kalan verilerin yetim kalmasını engelleme)
    elseif ($_POST['islem'] == 'admin_sil') {
        $silinecek_id = (int)$_POST['admin_id'];
        
        if ($silinecek_id != $_SESSION['kullanici_id']) {
            // Önce yöneticinin yapmış olabileceği yorum/puanları siliyoruz (Veritabanı temizliği)
            $db->prepare("DELETE FROM comments WHERE user_id = ?")->execute([$silinecek_id]);
            $db->prepare("DELETE FROM favorites WHERE user_id = ?")->execute([$silinecek_id]);
            $db->prepare("DELETE FROM ratings WHERE user_id = ?")->execute([$silinecek_id]);
            
            $sil = $db->prepare("DELETE FROM users WHERE id = ? AND rol = 'admin'");
            if ($sil->execute([$silinecek_id])) {
                $mesaj = "Yönetici hesabı kalıcı olarak silindi.";
                $mesaj_tur = "success";
            }
        }
    }
}

// SAYFA YÜKLENİRKEN MEVCUT AYARLARI ÇEK
$ayarSorgu = $db->query("SELECT * FROM site_settings WHERE id = 1");
$ayarlar = $ayarSorgu->fetch(PDO::FETCH_ASSOC);

// MEVCUT ADMİNLERİ ÇEK (Süper admin kendini listede görüp silemesin diye hariç tutuyoruz)
$adminlerSorgu = $db->query("SELECT id, username, email, created_at FROM users WHERE rol = 'admin' ORDER BY created_at DESC");
$admin_listesi = $adminlerSorgu->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="container content">
    <div class="admin-shell">
        
        <div class="admin-sidebar">
            <a href="admin_panel.php">📊 Dashboard</a>
            <a href="kullanicilar.php">👥 Kullanıcı Yönetimi</a>
            <a href="admin_profil.php">👤 Profil Ayarları</a>
            <a href="site_ayarlari.php" class="aktif">⚙️ Site Ayarları</a>
        </div>

        <div class="admin-content">
            <h2 class="admin-baslik">Merkezi Site Ayarları</h2>

            <?php if(!empty($mesaj)): ?>
                <div class="bildirim <?php echo $mesaj_tur; ?>">
                    <?php echo $mesaj; ?>
                </div>
            <?php endif; ?>

            <div class="profil-layout" style="grid-template-columns: 1fr 1fr; gap: 20px;">
                
                <div class="profil-kart">
                    <h3>Genel Bilgiler</h3>
                    <form method="POST">
                        <input type="hidden" name="islem" value="site_guncelle">
                        <div class="form-group">
                            <label>Site (Sekme) Başlığı</label>
                            <input type="text" name="site_title" value="<?php echo htmlspecialchars($ayarlar['site_title']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Footer Hakkımızda Metni</label>
                            <input type="text" name="footer_about" value="<?php echo htmlspecialchars($ayarlar['footer_about']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Instagram Linki</label>
                            <input type="text" name="instagram_link" value="<?php echo htmlspecialchars($ayarlar['instagram_link']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Twitter / X Linki</label>
                            <input type="text" name="twitter_link" value="<?php echo htmlspecialchars($ayarlar['twitter_link']); ?>">
                        </div>
                        <button type="submit" class="btn-kaydet">Ayarları Güncelle</button>
                    </form>
                </div>

                <div class="profil-kart">
                    <h3>Yeni Yönetici (Admin) Ekle</h3>
                    <form method="POST">
                        <input type="hidden" name="islem" value="admin_ekle">
                        <div class="form-group">
                            <label>Kullanıcı Adı</label>
                            <input type="text" name="username" required>
                        </div>
                        <div class="form-group">
                            <label>E-Posta Adresi</label>
                            <input type="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label>Geçici Parola</label>
                            <input type="password" name="sifre" required>
                        </div>
                        <button type="submit" class="btn-kaydet" style="background-color: #28a745;">Admin Olarak Yetkilendir</button>
                    </form>
                </div>

            </div>

            <div class="profil-kart" style="margin-top: 20px;">
                <h3>Mevcut Yöneticiler (Süper Admin Hariç)</h3>
                <?php if(count($admin_listesi) > 0): ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Kullanıcı Adı</th>
                                <th>E-Posta</th>
                                <th>Eklenme Tarihi</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($admin_listesi as $a): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($a['username']); ?></td>
                                <td><?php echo htmlspecialchars($a['email']); ?></td>
                                <td><?php echo date("d.m.Y", strtotime($a['created_at'])); ?></td>
                                <td>
                                    <form method="POST" onsubmit="return confirm('Bu yöneticiyi kalıcı olarak silmek istediğinize emin misiniz?');">
                                        <input type="hidden" name="islem" value="admin_sil">
                                        <input type="hidden" name="admin_id" value="<?php echo $a['id']; ?>">
                                        <button type="submit" style="background:transparent; border:none; color:#e50914; cursor:pointer; font-weight:bold; text-decoration:underline;">Yetkiyi Al & Sil</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: #888;">Henüz eklenmiş başka bir yönetici bulunmuyor.</p>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>