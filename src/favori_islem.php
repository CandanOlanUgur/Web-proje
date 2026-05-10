<?php
    session_start();
    require 'db.php';

    // Güvenlik: Giriş yapmamış kullanıcıyı engelle [cite: 21]
    if (!isset($_SESSION['kullanici_id'])) {
        header("Location: index.php");
    exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['movie_id'])) {
        $user_id = $_SESSION['kullanici_id'];
        $movie_id = (int)$_POST['movie_id'];

        try {
            // Önce bu film zaten favorilerde mi diye bakıyoruz 
            $kontrol = $db->prepare("SELECT id FROM favorites WHERE user_id = ? AND movie_id = ?");
            $kontrol->execute([$user_id, $movie_id]);

            if ($kontrol->rowCount() > 0) {
                // Varsa: Favorilerden Çıkar
                $sil = $db->prepare("DELETE FROM favorites WHERE user_id = ? AND movie_id = ?");
                $sil->execute([$user_id, $movie_id]);
                $_SESSION['etkilesim_mesaj'] = "Film favorilerinizden çıkarıldı.";
            } else {
                // Yoksa: Favorilere Ekle
                $ekle = $db->prepare("INSERT INTO favorites (user_id, movie_id) VALUES (?, ?)");
                $ekle->execute([$user_id, $movie_id]);
                $_SESSION['etkilesim_mesaj'] = "Film favorilerinize eklendi!";
            }

            // Kullanıcıyı geldiği detay sayfasına geri gönder 
            header("Location: film-detay.php?id=" . $movie_id);
            exit;
        } catch (PDOException $e) {
            $_SESSION['etkilesim_mesaj'] = "Bir hata oluştu: " . $e->getMessage();
            header("Location: film-detay.php?id=" . $movie_id);
            exit;
        }
    }
?>