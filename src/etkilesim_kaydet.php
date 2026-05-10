<?php
    session_start();
    require 'db.php';

    // Kullanıcı giriş yapmamışsa ana sayfaya at
    if (!isset($_SESSION['kullanici_id'])) {
        header("Location: index.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        // Formdan gelen verileri al ve temizle
        $user_id = $_SESSION['kullanici_id'];
        $movie_id = (int)$_POST['movie_id'];
        $puan = (int)$_POST['puan'];
        $yorum = trim($_POST['yorum']);

        //Güvenlik kontrolü: Puan gerçekten 1 ile 10 arasında mı?
        if ($puan < 1 || $puan > 10) {
            $_SESSION['etkilesim_mesaj'] = "Hata: Puan 1 ile 10 arasında olmalıdır.";
            header("Location: film-detay.php?id=" . $movie_id);
            exit;
        }

        try {
            // 1. YORUMU KAYDET (Prepared Statements ile güvenli kayıt)
            if (!empty($yorum)) {
                $yorum_ekle = $db->prepare("INSERT INTO comments (user_id, movie_id, comment_text) VALUES (?, ?, ?)");
                $yorum_ekle->execute([$user_id, $movie_id, $yorum]);
            }

            // 2. PUANI KAYDET VEYA GÜNCELLE
            // Önce kullanıcının bu filme daha önceden puan verip vermediğine bakıyoruz
            $puan_kontrol = $db->prepare("SELECT id FROM ratings WHERE user_id = ? AND movie_id = ?");
            $puan_kontrol->execute([$user_id, $movie_id]);

            if ($puan_kontrol->rowCount() > 0) {
                // Zaten puan vermiş, o zaman eski puanını güncelleyelim (UPDATE)
                $puan_guncelle = $db->prepare("UPDATE ratings SET rating_value = ? WHERE user_id = ? AND movie_id = ?");
                $puan_guncelle->execute([$puan, $user_id, $movie_id]);
            } else {
                // İlk defa puan veriyor, yeni kayıt açalım (INSERT)
                $puan_ekle = $db->prepare("INSERT INTO ratings (user_id, movie_id, rating_value) VALUES (?, ?, ?)");
                $puan_ekle->execute([$user_id, $movie_id, $puan]);
            }

            // İşlem başarılıysa mesajı kaydet ve geldiği filme geri gönder
            $_SESSION['etkilesim_mesaj'] = "Değerlendirmeniz başarıyla kaydedildi!";
            header("Location: film-detay.php?id=" . $movie_id);
            exit;
        } catch(PDOException $e) {
            // Veritabanında beklenmedik bir hata olursa
            $_SESSION['etkilesim_mesaj'] = "Kayıt sırasında bir hata oluştu: " . $e->getMessage();
            header("Location: film-detay.php?id=" . $movie_id);
            exit;
        }
    } else {
        header("Location: index.php");
        exit;
    }
?>    