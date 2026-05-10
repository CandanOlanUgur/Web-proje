<?php
$host = "localhost";
$dbname = "film_blog_db";
$username = "root";
$password = "";

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    //Hata modu exception
    $db -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}   catch(PDOException $e) {
    die("Veritabani baglanti hatasi: " . $e -> getMessage());
}
?>