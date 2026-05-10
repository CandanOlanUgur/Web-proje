<?php
    //oturum baslatilmadıysa baslat
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
?>

<!DOCTYPE html>
<html lang = "tr">
    <head>
        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale = 1.0">
        <title>FilmBlog</title>
        <link rel = "stylesheet" href = "style.css">
    </head>    

    <body>
        <header>
            <div class = "header-container">
                <nav>
                <div class="nav-left">
                    <a href="#" class="logo"><span>Film</span>Blog</a>

                    <ul class="nav-menu">
                        <li><a href="diziler.php">Diziler</a></li>
                        <li><a href="filmler.php">Filmler</a></li>
                        <li><a href="#">Popüler</a></li>
                        <li class="dropdown">
                            <input type="checkbox" id="menu-ac-kapa">

                            <label for="menu-ac-kapa" class="menu-btn">
                                Kategoriler ▼
                            </label>

                            <ul class="dropdown-menu">
                                <li><a href="#">Aksiyon</a></li>
                                <li><a href="#">Aile</a></li>
                                <li><a href="#">Animasyon</a></li>
                                <li><a href="#">Bilim Kurgu</a></li>
                                <li><a href="#">Çizgi Film</a></li>
                                <li><a href="#">Fantastik</a></li>
                                <li><a href="#">Gizem</a></li>
                                <li><a href="#">Korku</a></li>
                                <li><a href="#">Komedi</a></li>
                                <li><a href="#">Macera</a></li>
                                <li><a href="#">Polisiye</a></li>
                                <li><a href="#">Romantik</a></li>
                                <li><a href="#">Western</a></li>
                                <li><a href="#">Dram</a></li>
                                <li><a href="#">Gerilim</a></li>
                                <li><a href="#">Yerli</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
                
                <div class="nav-right">
                    <form action="#" class="search-form">
                        <input type="text" placeholder="Film veya Dizi Ara...">
                        <button type="submit">Ara</button>
                    </form> 

                    <div class="authButtons">
                        <?php if (isset($_SESSION['kullanici_id'])): ?>
                            
                            <span style="color: white; margin-right: 15px; align-self: center; font-size: 14px;">
                                Hoş Geldin, <strong style ="color: #e50914;"><?php echo htmlspecialchars($_SESSION['kullanici_adi']); ?></strong>
                            </span>
                            
                            <?php if ($_SESSION['kullanici_rolu'] === 'admin'): ?>
                                <a href="admin.php" class="btn-giris">Panel</a>
                            <?php else: ?>
                                <a href="profil.php" class="btn-giris">Profilim</a>
                            <?php endif; ?>
                            
                            <a href="cikis.php" class="btn-kayit">Çıkış Yap</a>

                        <?php else: ?>

                            <a href="#giris-popup" class="btn-giris">Giriş Yap</a>
                            <a href="#kayit-popup" class="btn-kayit">Kayıt Ol</a>

                        <?php endif; ?>    
                        <!--
                        <a href="#giris-popup" class="btn-giris">Giriş Yap</a>
                        <a href="#kayit-popup" class="btn-kayit">Kayıt Ol</a>
                        -->
                    </div>
                </div>
            </nav>
            </div>
        </header>   
    