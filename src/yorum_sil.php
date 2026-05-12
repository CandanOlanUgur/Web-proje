<?php
    session_start();
    require 'db.php';

    // Güvenlik: Sadece admin silebilir
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['yorum_id'])) {
        // Aktif kullanıcının rolünü tekrar kontrol edelim (Güvenlik için)
        $rolSorgu = $db->prepare("SELECT rol FROM users WHERE id = ?");
        $rolSorgu->execute([$_SESSION['kullanici_id']]);
        $rol = $rolSorgu->fetchColumn();

        if ($rol == 'admin' || $rol == 'super_admin') {
            $sil = $db->prepare("DELETE FROM comments WHERE id = ?");
            $sil->execute([$_POST['yorum_id']]);
            $_SESSION['etkilesim_mesaj'] = "Yorum başarıyla silindi.";
            header("Location: film-detay.php?id=" . $_POST['movie_id']);
            exit;
        }
        header("Location: film-detay.php?id=" . $_POST['movie_id']);
        exit;
    }

    header("Location: index.php");
?>
