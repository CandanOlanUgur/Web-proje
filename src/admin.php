<?php
session_start();

// Kullanıcı giriş yapmamışsa veya rolü admin değilse erişimi engelle
if (!isset($_SESSION["kullanici_id"]) || $_SESSION["kullanici_rolu"] !== "admin") {
    header("Location: index.php");
    exit;    
}
?>