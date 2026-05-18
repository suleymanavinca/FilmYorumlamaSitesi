<style>
    .footer {
        background: #2d2d2d;
        color: #fff;
        padding: 50px 30px 20px;
        margin-top: 80px;
    }

    .footer-container {
        max-width: 1400px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 40px;
        margin-bottom: 30px;
    }

    .footer-section h3 {
        color: #9d7cce;
        margin-bottom: 20px;
        font-size: 1.2em;
    }

    .footer-section ul {
        list-style: none;
    }

    .footer-section ul li {
        margin-bottom: 10px;
    }

    .footer-section a {
        color: #ccc;
        text-decoration: none;
        transition: color 0.3s;
    }

    .footer-section a:hover {
        color: #9d7cce;
    }

    .footer-section p {
        color: #ccc;
        line-height: 1.6;
    }

    .social-links {
        display: flex;
        gap: 15px;
        margin-top: 15px;
    }

    .social-links a {
        width: 40px;
        height: 40px;
        background: rgba(157, 124, 206, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2em;
        transition: all 0.3s;
    }

    .social-links a:hover {
        background: #9d7cce;
        transform: translateY(-3px);
    }

    .footer-bottom {
        border-top: 1px solid #444;
        padding-top: 20px;
        text-align: center;
        color: #999;
        font-size: 0.9em;
    }

    .footer-bottom a {
        color: #9d7cce;
        text-decoration: none;
    }

    .footer-bottom a:hover {
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .footer-container {
            grid-template-columns: 1fr;
            gap: 30px;
        }
    }
</style>

<footer class="footer">
    <div class="footer-container">
        <div class="footer-section">
            <h3>🎬 FilmKutusu</h3>
            <p>Film severler için tasarlanmış, sınırsız keşif ve paylaşım platformu. Binlerce film, yüzlerce yorum ve samimi bir topluluk!</p>
            <div class="social-links">
                <a href="#" title="Facebook">📘</a>
                <a href="#" title="Twitter">🐦</a>
                <a href="#" title="Instagram">📷</a>
                <a href="#" title="YouTube">▶️</a>
            </div>
        </div>

        <div class="footer-section">
            <h3>Hızlı Linkler</h3>
            <ul>
                <li><a href="index.php">Ana Sayfa</a></li>
                <li><a href="movies.php">Filmler</a></li>
                <li><a href="forum.php">Forum</a></li>
                <li><a href="about.php">Hakkımızda</a></li>
                <li><a href="contact.php">İletişim</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h3>Kategoriler</h3>
            <ul>
                <li><a href="movies.php?genre=aksiyon">Aksiyon</a></li>
                <li><a href="movies.php?genre=komedi">Komedi</a></li>
                <li><a href="movies.php?genre=drama">Drama</a></li>
                <li><a href="movies.php?genre=korku">Korku</a></li>
                <li><a href="movies.php?genre=bilim-kurgu">Bilim Kurgu</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h3>Yardım & Destek</h3>
            <ul>
                <li><a href="faq.php">Sık Sorulan Sorular</a></li>
                <li><a href="privacy.php">Gizlilik Politikası</a></li>
                <li><a href="terms.php">Kullanım Şartları</a></li>
                <li><a href="support.php">Destek</a></li>
                <li><a href="report.php">Sorun Bildir</a></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> FilmKutusu. Tüm hakları saklıdır. 
        <a href="privacy.php">Gizlilik</a> | 
        <a href="terms.php">Şartlar</a></p>
    </div>
</footer>

</body>
</html>