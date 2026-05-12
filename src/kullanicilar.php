<?php
    session_start();
    require 'db.php';

    // 1. GÜVENLİK (Yetki Kontrolü)
    if (!isset($_SESSION['kullanici_id'])) {
        header("Location: index.php");
        exit;
    }   

    $rolSorgu = $db->prepare("SELECT rol FROM users WHERE id = ?");
    $rolSorgu->execute([$_SESSION['kullanici_id']]);
    $aktif_rol = $rolSorgu->fetchColumn();

    if ($aktif_rol != 'admin' && $aktif_rol != 'super_admin') {
        // Yetkisiz erişim denemesi (Yönergedeki madde)
        header("Location: index.php"); 
        exit;
    }

    // 2. TOPLU SİLME İŞLEMİ (POST GELİRSE)
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['toplu_sil'])) {
        if (!empty($_POST['secili_kullanicilar'])) {
            // Gelen ID'leri güvenli bir string'e çevir (Örn: "5,8,12")
            $idler = implode(',', array_map('intval', $_POST['secili_kullanicilar']));
        
            // Super_admin silinemez kuralını ekliyoruz
            $topluSil = $db->query("DELETE FROM users WHERE id IN ($idler) AND rol != 'super_admin'");
            $mesaj = "Seçili kullanıcılar başarıyla silindi.";
            $mesaj_tur = "success";
        } else {
            $mesaj = "Silmek için en az bir kullanıcı seçmelisiniz.";
            $mesaj_tur = "error";
        }
    }

    // 3. KULLANICILARI VERİTABANINDAN ÇEK (Kendisi hariç diğer adminleri ve user'ları görsün)
    $kullanicilarSorgu = $db->prepare("SELECT id, username, email, rol, created_at FROM users WHERE rol != 'super_admin' ORDER BY created_at DESC");
    $kullanicilarSorgu->execute();
    $kullanici_listesi = $kullanicilarSorgu->fetchAll(PDO::FETCH_ASSOC);

    include 'header.php';
?>    

<div class="admin-container">
    <h2 class="admin-baslik">Kullanıcı Yönetimi</h2>

    <?php if(!empty($mesaj)): ?>
        <div style="background-color: <?php echo ($mesaj_tur == 'success') ? '#28a745' : '#dc3545'; ?>; color: white; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
            <?php echo $mesaj; ?>
        </div>
    <?php endif; ?>

    <form method="POST" onsubmit="return confirm('Seçili kullanıcıları ve onların tüm yorum/puanlarını kalıcı olarak silmek istediğinize emin misiniz?');">
        
        <div class="toolbar">
            <input type="text" id="aramaKutusu" class="arama-kutusu" placeholder="Kullanıcı Adı veya @E-posta ara...">
            <button type="submit" name="toplu_sil" class="btn-toplu-sil">Seçilileri Sil</button>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 50px;">
                        <input type="checkbox" id="hepsiniSec" style="cursor:pointer;">
                    </th>
                    <th>ID</th>
                    <th>Kullanıcı Adı</th>
                    <th>E-Posta Adresi</th>
                    <th>Yetki</th>
                    <th>Kayıt Tarihi</th>
                </tr>
            </thead>
            <tbody id="kullaniciTablosu">
                <?php foreach($kullanici_listesi as $k): ?>
                    <tr class="kullanici-satir">
                        <td>
                            <input type="checkbox" name="secili_kullanicilar[]" value="<?php echo $k['id']; ?>" class="secim-kutusu" style="cursor:pointer;">
                        </td>
                        <td>#<?php echo $k['id']; ?></td>
                        <td class="td-username"><?php echo htmlspecialchars($k['username']); ?></td>
                        <td class="td-email"><?php echo htmlspecialchars($k['email']); ?></td>
                        <td>
                            <span class="etiket-rol <?php echo ($k['rol'] == 'admin') ? 'rol-admin' : 'rol-user'; ?>">
                                <?php echo strtoupper($k['rol']); ?>
                            </span>
                        </td>
                        <td><?php echo date("d.m.Y H:i", strtotime($k['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </form>
</div>

<script>
    // 1. DİNAMİK ARAMA (Live Search)
    document.getElementById('aramaKutusu').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('.kullanici-satir');

        rows.forEach(row => {
            let username = row.querySelector('.td-username').textContent.toLowerCase();
            let email = row.querySelector('.td-email').textContent.toLowerCase();
            
            // Eğer arama kelimesinde "@" varsa, sadece e-posta içinde ara
            if (filter.includes('@')) {
                if (email.includes(filter)) { row.style.display = ''; } 
                else { row.style.display = 'none'; }
            } 
            // "@" yoksa hem kullanıcı adı hem e-posta içinde ara
            else {
                if (username.includes(filter) || email.includes(filter)) { row.style.display = ''; } 
                else { row.style.display = 'none'; }
            }
        });
    });

    // 2. TÜMÜNÜ SEÇ / KALDIR İŞLEMİ
    document.getElementById('hepsiniSec').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.secim-kutusu');
        checkboxes.forEach(cb => {
            // Sadece ekranda görünenleri (filtrelenmemiş olanları) seç
            if(cb.closest('.kullanici-satir').style.display !== 'none'){
                cb.checked = this.checked;
            }
        });
    });
</script>    

<?php include 'footer.php'; ?>