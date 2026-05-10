<?php
session_start();

// Tüm session (oturum) değişkenlerini sil
session_unset();

// Oturumu tamamen yok et
session_destroy();

// Ana sayfaya yönlendir
header("Location: index.php");
exit;
?>