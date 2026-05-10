    <footer>
        <div class = "footer-container">
            <div class = "footer-row">
                <div class = "footer-col">
                    <h3 class = "footer-logo">Film<span>Blog</span></h3>
                    <p> En güncel film ve dizi incelemeleri, fragmanlar ve sinema dünyasından haberler. </p>
                </div>
            
                <div class = "footer-col">
                    <h4>Kurumsal</h4>
                    <ul>
                        <li><a href = "#">Hakkımızda</a></li>
                        <li><a href = "#">İletişim</a></li>
                        <li><a href="#">Kullanım Koşulları</a></li>
                        <li><a href="#">Gizlilik Politikası</a></li>
                    </ul>
                </div>

                <div class = "footer-col">
                    <h4> Takip et</h4>
                    <div class = "social-links">
                        <a href  = "#">Instagram</a>
                        <a href = "#">Twitter / X </a>
                        <a href = "#">Youtube</a>
                    </div>
                </div>
            </div>            

            <div class = "footer-bottom">
                <p>&copy; 2026 FilmBlog. Tüm hakları saklıdır.</p>
            </div>
        </div>      
    </footer>        

    


    <!--<div id = "giris-popup" class = "popup-kapsayici">
        <div class = "popup-kutusu">
            <a href = "#!" class = "kapat-tusu">X</a>   

            <h2> Giriş Yap </h2>
            <form>
                <input type = "text" placeholder = "Kullanıcı Adı">
                <input type = "password" placeholder = "Şifre">
                <button type = "submit">Giriş</button> 
            </form>
        </div>
    </div> -->
    
    <div id ="giris-popup" class = "popup-kapsayici">
        <div class = "popup-kutusu">
            <a href = "#!" class = "kapat-tusu">X</a>
            <h2> Giriş Yap</h2>
            
            <?php
            if (isset($_SESSION['giris_hatasi'])) {
                echo '<div style="color: #e50914; margin-bottom: 15px; font-size: 14px; text-align: center; font-weight: bold;">' . $_SESSION['giris_hatasi'] . '</div>';
                unset($_SESSION['giris_hatasi']);    
            }
            ?>
            
            <form action = "kullanici_giris.php" method="POST">
                <input type = "text" name = "kullanici_adi" placeholder = "Kullanıcı Adı" required>
                <input type = "password" name = "sifre" placeholder = "Parola" required>
                <button type = "submit" name = "giris_yap">Gir</button>
            </form>
        </div>
    </div>

    <div id="kayit-popup" class="popup-kapsayici">
        <div class="popup-kutusu">
            <a href="#!" class="kapat-tusu">X</a>
            <h2>Kayıt Ol</h2>

            <?php
            if (isset($_SESSION['kayit_hatasi'])) {
                // Kırmızı renkli hata mesajını yazdır
                echo '<div style="color: #e50914; margin-bottom: 15px; font-size: 14px; text-align: center; font-weight: bold;">' . $_SESSION['kayit_hatasi'] . '</div>';
                // Mesaj bir kere gösterildikten sonra silinsin
                unset($_SESSION['kayit_hatasi']); 
            }
            ?>

            <form action="kayit_ol.php" method="POST">
                <input type="email" name="eposta" placeholder="E-posta" required>
                <input type="text" name="kullanici_adi" placeholder="Kullanıcı Adı" required>
                <input type="password" name="sifre" placeholder="Şifre" required>
                <button type="submit" name="kayit_ol">Kayıt Ol</button>
            </form> 
        </div>
    </div>
            <!--<h2> Kayit Ol</h2>
            <form>
                <input type = "text" placeholder = "E-Posta">
                <input type="text" placeholder="Kullanıcı Adı">
                <input type="password" placeholder="Şifre">
                <input type="password" placeholder="Şifre Tekrar">
                <button type="submit">Kayıt Ol</button>
            </form>
            -->
    
    
</body>
</html>                